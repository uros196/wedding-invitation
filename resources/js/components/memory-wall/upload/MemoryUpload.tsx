import { Loader2, Upload } from 'lucide-react';
import { useEffect, useRef } from 'react';
import {
    createMemoryUploadLabels,
    memoryWallCopy,
} from '@/components/memory-wall/copy';
import { useDelayedCompletedItems } from '@/hooks/use-delayed-completed-items';
import { useMemoryWallUpload } from '@/hooks/use-memory-wall-upload';
import { fonts, palette } from '../../invitation/theme';
import MemoryUploadCompletedList from './MemoryUploadCompletedList';
import MemoryUploadDropzone from './MemoryUploadDropzone';
import MemoryUploadItemList from './MemoryUploadItemList';
import MemoryUploadStatusListener from './MemoryUploadStatusListener';
import type { MemoryUploadProps } from './types';

/**
 * Render the drop zone, queue controls, and per-file upload status.
 */
export default function MemoryUpload({
    wedding,
    config,
    onMediaUploaded,
}: MemoryUploadProps) {
    const labels = createMemoryUploadLabels(config);
    const reportedMediaIds = useRef(new Set<number>());
    const {
        items,
        inputError,
        hasQueuedItems,
        isUploading,
        addFiles,
        startUploads,
        retryUpload,
        removeItem,
        handleProcessedEvent,
    } = useMemoryWallUpload({
        weddingUuid: wedding.uuid,
        config,
        messages: {
            fileTypeError: labels.fileTypeError,
            fileSizeError: labels.fileSizeError,
            maxFilesError: labels.maxFilesError,
            networkError: labels.networkError,
            processing: labels.processing,
        },
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
        queued: labels.queued,
        uploading: labels.uploading,
        processing: labels.processing,
        completed: labels.completed,
        error: labels.failed,
    };
    const hasProcessingItems = items.some(
        (item) => item.status === 'processing',
    );
    const { activeItems, completedItems } = useDelayedCompletedItems(items);

    return (
        <>
            <MemoryUploadStatusListener
                weddingUuid={wedding.uuid}
                onProcessed={handleProcessedEvent}
            />
            <section
                className="flex w-full flex-col items-center justify-start px-4 pt-14 pb-12 sm:pt-20"
                style={{
                    backgroundColor: palette.background,
                    backgroundImage:
                        'radial-gradient(circle at 15% 15%, rgba(152, 117, 166, 0.12), transparent 34%), radial-gradient(circle at 85% 0%, rgba(11, 47, 91, 0.08), transparent 30%)',
                    fontFamily: fonts.serif,
                }}
                aria-labelledby="memory-wall-upload-title"
            >
                <div
                    className="w-full max-w-3xl rounded-[2rem] p-2 text-center"
                    style={{
                        backgroundColor: 'rgba(255, 255, 255, 0.22)',
                        border: '1px solid rgba(255, 255, 255, 0.55)',
                        boxShadow: '0 24px 70px rgba(67, 58, 102, 0.12)',
                    }}
                >
                    <div
                        className="rounded-[1.5rem] p-5 sm:p-9"
                        style={{
                            backgroundColor: 'rgba(255, 255, 255, 0.34)',
                            border: '1px solid rgba(67, 58, 102, 0.12)',
                        }}
                    >
                        <div
                            className="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-full"
                            style={{
                                backgroundColor: 'rgba(67, 58, 102, 0.08)',
                            }}
                        >
                            <Upload
                                size={24}
                                style={{ color: palette.celestial }}
                            />
                        </div>

                        <h3
                            id="memory-wall-upload-title"
                            className="mb-3 text-3xl font-medium tracking-wide"
                            style={{ color: palette.deep }}
                        >
                            {labels.title}
                        </h3>
                        <p
                            className="mx-auto mb-5 max-w-xl text-base leading-relaxed"
                            style={{ color: palette.dawn }}
                        >
                            {labels.description}
                        </p>

                        <form
                            onSubmit={(event) => {
                                event.preventDefault();
                                startUploads();
                            }}
                            className="space-y-4"
                        >
                            <MemoryUploadDropzone
                                config={config}
                                labels={labels}
                                onFilesSelected={addFiles}
                            />
                            <div
                                className="flex flex-col gap-1 text-left text-xs sm:flex-row sm:justify-between"
                                style={{ color: palette.dawn }}
                            >
                                <span>{labels.maxFiles}</span>
                                <span>{labels.maxFileSize}</span>
                            </div>
                            <p
                                className="text-left text-xs"
                                style={{ color: palette.celestial }}
                                role="status"
                            >
                                {config.autoUpload
                                    ? memoryWallCopy.upload.autoUploadHint
                                    : memoryWallCopy.upload.manualUploadHint}
                            </p>

                            {inputError && (
                                <p
                                    className="text-left text-xs text-red-700"
                                    role="alert"
                                >
                                    {inputError}
                                </p>
                            )}

                            <MemoryUploadItemList
                                items={activeItems}
                                labels={labels}
                                statusLabels={statusLabels}
                                onRetry={retryUpload}
                                onRemove={removeItem}
                            />

                            {!config.autoUpload && hasQueuedItems && (
                                <button
                                    type="submit"
                                    disabled={isUploading}
                                    className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-full px-5 py-3 text-sm font-medium shadow-[0_12px_24px_rgba(11,47,91,0.16)] transition-[transform,opacity] duration-300 ease-[cubic-bezier(0.32,0.72,0,1)] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                                    style={{
                                        backgroundColor: palette.deep,
                                        color: palette.background,
                                    }}
                                >
                                    {isUploading && (
                                        <Loader2
                                            size={16}
                                            className="animate-spin"
                                        />
                                    )}
                                    <span>
                                        {isUploading
                                            ? hasProcessingItems
                                                ? labels.processing
                                                : labels.uploading
                                            : labels.uploadAction}
                                    </span>
                                </button>
                            )}
                        </form>

                        <MemoryUploadCompletedList
                            items={completedItems}
                            labels={labels}
                            statusLabels={statusLabels}
                            onRetry={retryUpload}
                            onRemove={removeItem}
                        />
                    </div>
                </div>
            </section>
        </>
    );
}
