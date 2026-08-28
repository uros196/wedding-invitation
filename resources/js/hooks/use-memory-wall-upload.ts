import { useCallback, useEffect, useRef, useState } from 'react';
import {
    cancelUpload,
    completeUpload,
    getUploadPartUrls,
    initializeUpload,
} from '@/actions/App/Http/Controllers/MemoryWallController';
import type { Media } from '@/types';

/** Lifecycle states shown for an item in the upload queue. */
export type MemoryUploadStatus = 'queued' | 'uploading' | 'completed' | 'error';

/** Client-side state kept for one selected image or video. */
export interface MemoryUploadItem {
    id: string;
    file: File;
    previewUrl: string;
    clientUploadId: string;
    uploadToken: string;
    uploadUuid: string | null;
    progress: number;
    status: MemoryUploadStatus;
    error: string | null;
    media: Media | null;
}

/** Limits and accepted MIME types supplied by the Laravel page response. */
export interface MemoryWallUploadConfig {
    maxFiles: number;
    maxFileSize: number;
    acceptedTypes: string[];
}

/** Only the messages needed by the transport/orchestration layer. */
export interface MemoryWallUploadTranslations {
    fileTypeError: string;
    fileSizeError: string;
    maxFilesError: string;
    networkError: string;
}

/** Data returned after a multipart session is initialized. */
interface InitializeResponse {
    data: {
        uuid: string;
        upload_token: string;
        part_size: number;
    };
}

/** Presigned part URLs returned by the Laravel coordinator. */
interface PartsResponse {
    data: {
        parts: Array<{ part_number: number; url: string }>;
    };
}

/** Media resource returned after S3 assembly and server-side validation. */
interface CompleteResponse {
    data: Media;
}

/** Configuration and localized messages required by the upload coordinator. */
interface UseMemoryWallUploadOptions {
    weddingUuid: string;
    config: MemoryWallUploadConfig;
    translations: MemoryWallUploadTranslations;
}

/** Public operations and state exposed to the upload component. */
interface UseMemoryWallUploadResult {
    items: MemoryUploadItem[];
    inputError: string | null;
    hasQueuedItems: boolean;
    isUploading: boolean;
    addFiles: (files: FileList | File[]) => void;
    startUploads: () => void;
    retryUpload: (id: string) => void;
    removeItem: (id: string) => void;
}

/** Supported error shapes returned by Laravel validation and JSON endpoints. */
interface UploadErrorResponse {
    message?: string;
    errors?: Record<string, string[]>;
}

/** Create a 256-bit token used to authorize subsequent session requests. */
function createUploadToken(): string {
    const bytes = new Uint8Array(32);
    crypto.getRandomValues(bytes);

    return Array.from(bytes, (byte) => byte.toString(16).padStart(2, '0')).join('');
}

/** Extract the most useful message from Laravel's JSON validation response. */
async function readError(response: Response, fallback: string): Promise<Error> {
    const body = (await response.json().catch(() => null)) as UploadErrorResponse | null;
    const message = body?.errors ? Object.values(body.errors).flat()[0] : body?.message;

    return new Error(message ?? fallback);
}

/** Send one JSON request to the Laravel part of the upload workflow. */
async function postJson<T>(
    url: string,
    body: Record<string, unknown>,
    signal: AbortSignal,
    fallback: string,
): Promise<T> {
    const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        signal,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    });

    if (!response.ok) {
        throw await readError(response, fallback);
    }

    return (await response.json()) as T;
}

/**
 * Upload one byte range directly to S3 and report its current progress.
 *
 * The request registry is used so removing an item can abort every active part
 * request belonging to that file, rather than leaving orphaned browser work.
 */
function uploadPart(
    url: string,
    file: File,
    start: number,
    end: number,
    signal: AbortSignal,
    onProgress: (loaded: number) => void,
    errorMessage: string,
    registerRequest: (request: XMLHttpRequest) => () => void,
): Promise<void> {
    return new Promise((resolve, reject) => {
        const request = new XMLHttpRequest();
        const unregisterRequest = registerRequest(request);

        request.open('PUT', url);
        request.upload.onprogress = (event) => {
            if (event.lengthComputable) {
                onProgress(event.loaded);
            }
        };
        request.onload = () => {
            unregisterRequest();

            if (request.status >= 200 && request.status < 300) {
                resolve();

                return;
            }

            reject(new Error(errorMessage));
        };
        request.onerror = () => {
            unregisterRequest();
            reject(new Error(errorMessage));
        };
        request.onabort = () => {
            unregisterRequest();
            reject(new DOMException('', 'AbortError'));
        };
        signal.addEventListener('abort', () => request.abort(), { once: true });
        request.send(file.slice(start, end));
    });
}

/** Create local preview and idempotency data for a newly selected file. */
function createItem(file: File): MemoryUploadItem {
    return {
        id: crypto.randomUUID(),
        file,
        previewUrl: URL.createObjectURL(file),
        clientUploadId: crypto.randomUUID(),
        uploadToken: createUploadToken(),
        uploadUuid: null,
        progress: 0,
        status: 'queued',
        error: null,
        media: null,
    };
}

/**
 * Manage independent multipart sessions for the memory wall drop zone.
 *
 * Up to three files are processed at once, and each file can upload up to
 * three S3 parts concurrently. This keeps a large video from blocking images
 * while preserving a separate progress and cancellation boundary per file.
 */
export function useMemoryWallUpload({
    weddingUuid,
    config,
    translations,
}: UseMemoryWallUploadOptions): UseMemoryWallUploadResult {
    const [items, setItems] = useState<MemoryUploadItem[]>([]);
    const [inputError, setInputError] = useState<string | null>(null);
    // One controller per file lets retry/cancel affect only that file.
    const controllers = useRef(new Map<string, AbortController>());
    // A file has several concurrent part requests, so retain all of them for cancellation.
    const requests = useRef(new Map<string, Set<XMLHttpRequest>>());
    const itemsRef = useRef<MemoryUploadItem[]>([]);

    /** Merge a partial state update without replacing the rest of an item. */
    const updateItem = useCallback((id: string, update: Partial<MemoryUploadItem>) => {
        setItems((currentItems) => currentItems.map((item) => (item.id === id ? { ...item, ...update } : item)));
    }, []);

    /** Track an active part request and return its unregister callback. */
    const registerRequest = useCallback((id: string, request: XMLHttpRequest): (() => void) => {
        const itemRequests = requests.current.get(id) ?? new Set<XMLHttpRequest>();
        itemRequests.add(request);
        requests.current.set(id, itemRequests);

        return () => {
            itemRequests.delete(request);
        };
    }, []);

    /** Tell Laravel to abort the remote session after a local cancellation. */
    const cancelSession = useCallback(
        async (item: MemoryUploadItem): Promise<void> => {
            if (!item.uploadUuid) {
                return;
            }

            const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
            // Cancellation is best effort here: the item is already removed
            // locally, and a transient cleanup failure must not block the UI.
            await fetch(cancelUpload({ wedding: weddingUuid, upload: item.uploadUuid }).url, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ upload_token: item.uploadToken }),
            });
        },
        [weddingUuid],
    );

    /**
     * Run the complete initialize, upload-parts, and complete sequence for one file.
     */
    const uploadItem = useCallback(
        async (item: MemoryUploadItem): Promise<void> => {
            // Every item gets its own signal so one failed or cancelled file
            // does not interrupt the other files in the queue.
            const controller = new AbortController();
            controllers.current.set(item.id, controller);
            updateItem(item.id, { status: 'uploading', error: null, progress: 0 });

            try {
                const initializeResponse = await postJson<InitializeResponse>(
                    initializeUpload(weddingUuid).url,
                    {
                        client_upload_id: item.clientUploadId,
                        upload_token: item.uploadToken,
                        file_name: item.file.name,
                        size: item.file.size,
                        mime_type: item.file.type,
                    },
                    controller.signal,
                    translations.networkError,
                );
                const uploadUuid = initializeResponse.data.uuid;
                updateItem(item.id, { uploadUuid });

                // Laravel signs each part separately; the file bytes never pass
                // through the application server.
                const partsResponse = await postJson<PartsResponse>(
                    getUploadPartUrls({ wedding: weddingUuid, upload: uploadUuid }).url,
                    { upload_token: item.uploadToken },
                    controller.signal,
                    translations.networkError,
                );
                const loadedParts = new Map<number, number>();
                let nextPartIndex = 0;
                // Run up to three part requests for this file. A separate set
                // of workers is created for every file session.
                const workers = Array.from(
                    { length: Math.min(3, partsResponse.data.parts.length) },
                    async (): Promise<void> => {
                        while (nextPartIndex < partsResponse.data.parts.length) {
                            const partIndex = nextPartIndex++;
                            const part = partsResponse.data.parts[partIndex];
                            const start = partIndex * initializeResponse.data.part_size;
                            const end = Math.min(start + initializeResponse.data.part_size, item.file.size);
                            await uploadPart(
                                part.url,
                                item.file,
                                start,
                                end,
                                controller.signal,
                                (loaded) => {
                                    loadedParts.set(part.part_number, loaded);
                                    const uploadedBytes = Array.from(loadedParts.values()).reduce(
                                        (total, value) => total + value,
                                        0,
                                    );
                                    // Reserve 100% for the completion response;
                                    // reaching the end of a PUT is not publication.
                                    updateItem(item.id, {
                                        progress: Math.min(99, Math.round((uploadedBytes / item.file.size) * 100)),
                                    });
                                },
                                translations.networkError,
                                (request) => registerRequest(item.id, request),
                            );
                            loadedParts.set(part.part_number, end - start);
                        }
                    },
                );
                await Promise.all(workers);

                // The server verifies the assembled object before returning
                // the media resource that can be shown in the gallery.
                const completeResponse = await postJson<CompleteResponse>(
                    completeUpload({ wedding: weddingUuid, upload: uploadUuid }).url,
                    { upload_token: item.uploadToken },
                    controller.signal,
                    translations.networkError,
                );
                updateItem(item.id, {
                    status: 'completed',
                    progress: 100,
                    media: completeResponse.data,
                });
            } catch (error) {
                if (error instanceof DOMException && error.name === 'AbortError') {
                    return;
                }

                updateItem(item.id, {
                    status: 'error',
                    error: error instanceof Error ? error.message : translations.networkError,
                });
            } finally {
                controllers.current.delete(item.id);
            }
        },
        [registerRequest, translations.networkError, updateItem, weddingUuid],
    );

    /** Validate and append selected files without starting network requests. */
    const addFiles = useCallback(
        (files: FileList | File[]): void => {
            const incomingFiles = Array.from(files);
            const rejectedByType = incomingFiles.some((file) => !config.acceptedTypes.includes(file.type));
            const rejectedBySize = incomingFiles.some((file) => file.size > config.maxFileSize);
            const acceptedFiles = incomingFiles.filter(
                (file) => config.acceptedTypes.includes(file.type) && file.size <= config.maxFileSize,
            );
            const availableSlots = Math.max(0, config.maxFiles - items.length);

            if (rejectedByType) {
                setInputError(translations.fileTypeError);
            } else if (rejectedBySize) {
                setInputError(translations.fileSizeError);
            } else if (acceptedFiles.length > availableSlots) {
                setInputError(translations.maxFilesError);
            } else {
                setInputError(null);
            }

            if (acceptedFiles.length === 0 || availableSlots === 0) {
                return;
            }

            setItems((currentItems) => [
                ...currentItems,
                ...acceptedFiles.slice(0, availableSlots).map(createItem),
            ]);
        },
        [config, items.length, translations],
    );

    /** Start queued files with a maximum of three independent file sessions. */
    const startUploads = useCallback((): void => {
        const queuedItems = items.filter((item) => item.status === 'queued');
        let nextItemIndex = 0;
        const startNext = (): void => {
            const item = queuedItems[nextItemIndex++];

            if (!item) {
                return;
            }

            void uploadItem(item).finally(startNext);
        };

        Array.from({ length: Math.min(3, queuedItems.length) }, startNext);
    }, [items, uploadItem]);

    /** Retry only the selected failed file, preserving completed siblings. */
    const retryUpload = useCallback(
        (id: string): void => {
            const item = items.find((candidate) => candidate.id === id);

            if (item?.status === 'error') {
                void uploadItem(item);
            }
        },
        [items, uploadItem],
    );

    /** Remove a file locally and cancel its remote session when necessary. */
    const removeItem = useCallback(
        (id: string): void => {
            const item = items.find((candidate) => candidate.id === id);

            if (!item) {
                return;
            }

            controllers.current.get(id)?.abort();
            requests.current.get(id)?.forEach((request) => request.abort());
            requests.current.delete(id);

            if (item.status !== 'completed') {
                void cancelSession(item).catch(() => undefined);
            }

            URL.revokeObjectURL(item.previewUrl);
            setItems((currentItems) => currentItems.filter((candidate) => candidate.id !== id));
        },
        [cancelSession, items],
    );

    // Keep the latest list available to the unmount cleanup without making the
    // cleanup effect depend on every progress update.
    useEffect(() => {
        itemsRef.current = items;
    }, [items]);

    // Prevent active browser requests and preview URLs from surviving a page
    // navigation or component unmount.
    useEffect(() => {
        const controllersOnUnmount = controllers.current;
        const requestsOnUnmount = requests.current;

        return () => {
            controllersOnUnmount.forEach((controller) => controller.abort());
            requestsOnUnmount.forEach((itemRequests) => itemRequests.forEach((request) => request.abort()));
            itemsRef.current.forEach((item) => URL.revokeObjectURL(item.previewUrl));
        };
    }, []);

    return {
        items,
        inputError,
        hasQueuedItems: items.some((item) => item.status === 'queued'),
        isUploading: items.some((item) => item.status === 'uploading'),
        addFiles,
        startUploads,
        retryUpload,
        removeItem,
    };
}