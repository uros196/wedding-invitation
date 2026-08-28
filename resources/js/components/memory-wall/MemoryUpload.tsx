import { Image as ImageIcon, Loader2, Upload, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useMemoryWallUpload } from '@/hooks/use-memory-wall-upload';
import type {
    MemoryUploadItem,
    MemoryWallUploadConfig,
    MemoryWallUploadTranslations,
} from '@/hooks/use-memory-wall-upload';
import type { Media, Wedding } from '@/types';
import { fonts, palette } from '../invitation/theme';

/** Props for the public memory wall upload panel. */
interface MemoryUploadProps {
    wedding: Wedding;
    config: MemoryWallUploadConfig;
    translations: {
        title: string;
        description: string;
        dropzone: string;
        browse: string;
        dropzoneHint: string;
        videoLabel: string;
        selected: string;
        uploadAction: string;
        uploading: string;
        queued: string;
        completed: string;
        failed: string;
        retry: string;
        cancel: string;
        remove: string;
        maxFiles: string;
        maxFileSize: string;
        fileTypeError: string;
        fileSizeError: string;
        maxFilesError: string;
        empty: string;
        networkError: string;
        completedSummary: string;
    };
    onMediaUploaded: (media: Media) => void;
}

/** Format byte counts for a compact value beside each selected file. */
function formatFileSize(bytes: number): string {
    if (bytes < 1024 * 1024) {
        return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

/**
 * Render an immediate local preview without requiring a server conversion.
 *
 * Browsers can preview videos through an object URL, which keeps the feature
 * useful even when server-side video thumbnail generation is unavailable.
 */
function Preview({ item, imageAlt, videoLabel }: { item: MemoryUploadItem; imageAlt: string; videoLabel: string }) {
    if (item.file.type.startsWith('video/')) {
        return (
            <div className="relative h-24 w-24 shrink-0 overflow-hidden rounded-lg bg-black/10">
                <video
                    src={item.previewUrl}
                    muted
                    playsInline
                    preload="metadata"
                    className="h-full w-full object-cover"
                />
                <span className="absolute right-1 bottom-1 rounded bg-black/60 px-1.5 py-0.5 text-[10px] text-white">
                    {videoLabel}
                </span>
            </div>
        );
    }

    return <img src={item.previewUrl} alt={imageAlt} className="h-24 w-24 shrink-0 rounded-lg object-cover" />;
}

/**
 * Render the drop zone, queue controls, and per-file upload status.
 */
export default function MemoryUpload({ wedding, config, translations, onMediaUploaded }: MemoryUploadProps) {
    const [isDragActive, setIsDragActive] = useState(false);
    const fileInput = useRef<HTMLInputElement>(null);
    const reportedMediaIds = useRef(new Set<number>());
    const uploadTranslations: MemoryWallUploadTranslations = {
        fileTypeError: translations.fileTypeError,
        fileSizeError: translations.fileSizeError,
        maxFilesError: translations.maxFilesError,
        networkError: translations.networkError,
    };
    const {
        items,
        inputError,
        hasQueuedItems,
        isUploading,
        addFiles,
        startUploads,
        retryUpload,
        removeItem,
    } = useMemoryWallUpload({
        weddingUuid: wedding.uuid,
        config,
        translations: uploadTranslations,
    });

    // Completed media is reported to the page once so the gallery can update
    // immediately without waiting for a full Inertia reload.
    useEffect(() => {
        items.forEach((item) => {
            if (item.media && !reportedMediaIds.current.has(item.media.id)) {
                reportedMediaIds.current.add(item.media.id);
                onMediaUploaded(item.media);
            }
        });
    }, [items, onMediaUploaded]);

    const statusLabels = {
        queued: translations.queued,
        uploading: translations.uploading,
        completed: translations.completed,
        error: translations.failed,
    };

    return (
        <section
            className="flex w-full flex-col items-center justify-start px-4 pt-16 pb-12 sm:pt-24"
            style={{ backgroundColor: palette.background, fontFamily: fonts.serif }}
            aria-labelledby="memory-wall-upload-title"
        >
            <div
                className="w-full max-w-3xl rounded-2xl p-6 text-center shadow-sm sm:p-8"
                style={{
                    backgroundColor: 'rgba(255, 255, 255, 0.3)',
                    border: '1px solid rgba(67, 58, 102, 0.15)',
                }}
            >
                <div
                    className="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-full"
                    style={{ backgroundColor: 'rgba(67, 58, 102, 0.08)' }}
                >
                    <Upload size={24} style={{ color: palette.celestial }} />
                </div>

                <h3 id="memory-wall-upload-title" className="mb-3 text-3xl font-medium tracking-wide" style={{ color: palette.deep }}>
                    {translations.title}
                </h3>
                <p className="mx-auto mb-6 max-w-xl text-base leading-relaxed" style={{ color: palette.dawn }}>
                    {translations.description}
                </p>

                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        // Uploads are started explicitly so guests can review
                        // the selected queue before any network traffic begins.
                        startUploads();
                    }}
                    className="space-y-4"
                >
                    <div
                        role="button"
                        tabIndex={0}
                        onClick={() => fileInput.current?.click()}
                        onKeyDown={(event) => {
                            if (event.key === 'Enter' || event.key === ' ') {
                                event.preventDefault();
                                fileInput.current?.click();
                            }
                        }}
                        onDragEnter={(event) => {
                            event.preventDefault();
                            setIsDragActive(true);
                        }}
                        onDragOver={(event) => event.preventDefault()}
                        onDragLeave={() => setIsDragActive(false)}
                        onDrop={(event) => {
                            event.preventDefault();
                            setIsDragActive(false);
                            addFiles(event.dataTransfer.files);
                        }}
                        className={`flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed p-6 transition-all hover:opacity-80 ${isDragActive ? 'ring-2 ring-offset-2' : ''}`}
                        style={{
                            borderColor: isDragActive ? palette.celestial : 'rgba(67, 58, 102, 0.25)',
                            backgroundColor: 'rgba(255, 255, 255, 0.5)',
                        }}
                    >
                        <input
                            ref={fileInput}
                            type="file"
                            multiple
                            accept={config.acceptedTypes.join(',')}
                            className="hidden"
                            onChange={(event) => {
                                if (event.target.files) {
                                    addFiles(event.target.files);
                                    event.target.value = '';
                                }
                            }}
                        />
                        <ImageIcon size={32} style={{ color: palette.deep }} />
                        <span className="text-sm font-medium" style={{ color: palette.deep }}>
                            {translations.dropzone}
                        </span>
                        <span className="text-xs" style={{ color: palette.dawn }}>
                            {translations.browse} · {translations.dropzoneHint}
                        </span>
                    </div>

                    <div className="flex flex-wrap justify-between gap-2 text-left text-xs" style={{ color: palette.dawn }}>
                        <span>{translations.maxFiles}</span>
                        <span>{translations.maxFileSize}</span>
                    </div>

                    {inputError && <p className="text-left text-xs text-red-600">{inputError}</p>}

                    {items.length > 0 && (
                        <div className="space-y-3 text-left" aria-live="polite">
                            <p className="text-sm font-medium" style={{ color: palette.deep }}>
                                {items.length} {translations.selected}
                            </p>
                            {items.map((item) => (
                                <UploadItemRow
                                    key={item.id}
                                    item={item}
                                    labels={translations}
                                    statusLabels={statusLabels}
                                    onRetry={retryUpload}
                                    onRemove={removeItem}
                                />
                            ))}
                        </div>
                    )}

                    {hasQueuedItems && (
                        <button
                            type="submit"
                            disabled={isUploading}
                            className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-medium shadow-sm transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-50"
                            style={{ backgroundColor: palette.deep, color: palette.background }}
                        >
                            {isUploading && <Loader2 size={16} className="animate-spin" />}
                            <span>{isUploading ? translations.uploading : translations.uploadAction}</span>
                        </button>
                    )}
                </form>

                {items.some((item) => item.status === 'completed') && (
                    <p className="mt-4 text-xs" style={{ color: palette.dawn }}>
                        {translations.completedSummary}
                    </p>
                )}
            </div>
        </section>
    );
}

/** Render one queue item with its preview, progress, retry, and remove actions. */
function UploadItemRow({
    item,
    labels,
    statusLabels,
    onRetry,
    onRemove,
}: {
    item: MemoryUploadItem;
    labels: MemoryUploadProps['translations'];
    statusLabels: Record<MemoryUploadItem['status'], string>;
    onRetry: (id: string) => void;
    onRemove: (id: string) => void;
}) {
    const removeLabel = item.status === 'uploading' ? labels.cancel : labels.remove;

    return (
        <div className="flex items-start gap-3 rounded-xl border border-black/10 bg-white/40 p-3">
            <Preview item={item} imageAlt={labels.title} videoLabel={labels.videoLabel} />
            <div className="min-w-0 flex-1">
                <p className="truncate text-sm font-medium" style={{ color: palette.deep }} title={item.file.name}>
                    {item.file.name}
                </p>
                <p className="mt-1 text-xs" style={{ color: palette.dawn }}>
                    {formatFileSize(item.file.size)} · {statusLabels[item.status]}
                </p>
                {item.status === 'uploading' && (
                    <div
                        className="mt-2 h-2 overflow-hidden rounded-full bg-black/10"
                        role="progressbar"
                        aria-valuenow={item.progress}
                        aria-valuemin={0}
                        aria-valuemax={100}
                    >
                        <div
                            className="h-full transition-[width] duration-200"
                            style={{ width: `${item.progress}%`, backgroundColor: palette.celestial }}
                        />
                    </div>
                )}
                {item.status === 'error' && <p className="mt-1 text-xs text-red-600">{item.error}</p>}
            </div>
            <div className="flex shrink-0 items-center gap-2">
                {item.status === 'error' && (
                    <button
                        type="button"
                        onClick={() => onRetry(item.id)}
                        className="text-xs font-medium underline underline-offset-2"
                        style={{ color: palette.deep }}
                    >
                        {labels.retry}
                    </button>
                )}
                <button
                    type="button"
                    onClick={() => onRemove(item.id)}
                    className="rounded-full p-1 transition-opacity hover:opacity-70"
                    style={{ color: palette.deep }}
                    aria-label={`${removeLabel}: ${item.file.name}`}
                >
                    <X size={16} />
                </button>
            </div>
        </div>
    );
}
