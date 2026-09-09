import { Loader2, Upload } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { useDelayedCompletedItems } from '@/hooks/use-delayed-completed-items';
import { useMemoryWallUpload } from '@/hooks/use-memory-wall-upload';
import type { MemoryWallUploadTranslations } from '@/hooks/use-memory-wall-upload';
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
    translations,
    onMediaUploaded,
}: MemoryUploadProps) {
    const reportedMediaIds = useRef(new Set<number>());
    const uploadTranslations: MemoryWallUploadTranslations = {
        fileTypeError: translations.fileTypeError,
        fileSizeError: translations.fileSizeError,
        maxFilesError: translations.maxFilesError,
        networkError: translations.networkError,
        processing: translations.processing,
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
        handleProcessedEvent,
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
        processing: translations.processing,
        completed: translations.completed,
        error: translations.failed,
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
                className="flex w-full flex-col items-center justify-start px-4 pt-16 pb-12 sm:pt-24"
                style={{
                    backgroundColor: palette.background,
                    fontFamily: fonts.serif,
                }}
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
                        <Upload
                            size={24}
                            style={{ color: palette.celestial }}
                        />
                    </div>

                    <h3 id="memory-wall-upload-title"
                        className="mb-3 text-3xl font-medium tracking-wide"
                        style={{ color: palette.deep }}
                    >
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
                        <MemoryUploadDropzone
                            config={config}
                            translations={translations}
                            onFilesSelected={addFiles}
                        />
                        <div className="flex flex-wrap justify-between gap-2 text-left text-xs" style={{ color: palette.dawn }}>
                            <span>{translations.maxFiles}</span>
                            <span>{translations.maxFileSize}</span>
                        </div>

                        {inputError && (
                            <p className="text-left text-xs text-red-600">
                                {inputError}
                            </p>
                        )}

                        <MemoryUploadItemList
                            items={activeItems}
                            labels={translations}
                            statusLabels={statusLabels}
                            onRetry={retryUpload}
                            onRemove={removeItem}
                        />

                        {hasQueuedItems && (
                            <button
                                type="submit"
                                disabled={isUploading}
                                className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-medium shadow-sm transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-50"
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
                                            ? translations.processing
                                            : translations.uploading
                                        : translations.uploadAction}
                                </span>
                            </button>
                        )}
                    </form>

                    <MemoryUploadCompletedList
                        items={completedItems}
                        labels={translations}
                        statusLabels={statusLabels}
                        onRetry={retryUpload}
                        onRemove={removeItem}
                    />
                </div>
            </section>
        </>
    );
}
