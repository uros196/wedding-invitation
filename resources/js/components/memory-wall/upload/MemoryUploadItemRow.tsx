import { ImageOff, Loader2, Video, X } from 'lucide-react';
import { useState } from 'react';
import type { MemoryUploadItem } from '@/hooks/use-memory-wall-upload';
import { palette } from '../../invitation/theme';
import type { MemoryUploadLabels, MemoryUploadStatusLabels } from './types';

function formatFileSize(bytes: number): string {
    if (bytes < 1024 * 1024) {
        return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

/** Render an immediate local preview without requiring a server conversion. */
function Preview({
    item,
    imageAlt,
    videoLabel,
}: {
    item: MemoryUploadItem;
    imageAlt: string;
    videoLabel: string;
}) {
    const isVideo = item.file.type.startsWith('video/');
    const [isLoading, setIsLoading] = useState(true);
    const [hasError, setHasError] = useState(false);

    const handleLoaded = () => {
        setIsLoading(false);
    };

    const handleError = () => {
        setIsLoading(false);
        setHasError(true);
    };

    return (
        <div className="relative h-24 w-24 shrink-0 overflow-hidden rounded-lg bg-black/10">
            {hasError ? (
                <div className="flex h-full w-full items-center justify-center text-black/40">
                    {isVideo ? (
                        <Video size={24} aria-hidden="true" />
                    ) : (
                        <ImageOff size={24} aria-hidden="true" />
                    )}
                </div>
            ) : isVideo ? (
                <video
                    src={item.previewUrl}
                    muted
                    playsInline
                    preload="metadata"
                    className="h-full w-full object-cover"
                    onLoadedData={handleLoaded}
                    onError={handleError}
                />
            ) : (
                <img
                    src={item.previewUrl}
                    alt={imageAlt}
                    className="h-full w-full object-cover"
                    onLoad={handleLoaded}
                    onError={handleError}
                />
            )}
            {isLoading && !hasError && (
                <div
                    className="absolute inset-0 flex items-center justify-center bg-black/10"
                    role="status"
                    aria-label="Loading preview"
                >
                    <Loader2
                        size={24}
                        className="animate-spin text-white"
                        aria-hidden="true"
                    />
                </div>
            )}
            {isVideo && (
                <span className="absolute right-1 bottom-1 rounded bg-black/60 px-1.5 py-0.5 text-[10px] text-white">
                    {videoLabel}
                </span>
            )}
        </div>
    );
}

interface MemoryUploadItemRowProps {
    item: MemoryUploadItem;
    labels: MemoryUploadLabels;
    statusLabels: MemoryUploadStatusLabels;
    onRetry: (id: string) => void;
    onRemove: (id: string) => void;
}

/** Render one queue item with its preview, progress, and actions. */
export default function MemoryUploadItemRow({
    item,
    labels,
    statusLabels,
    onRetry,
    onRemove,
}: MemoryUploadItemRowProps) {
    const removeLabel =
        item.status === 'uploading' ? labels.cancel : labels.remove;
    const isCompleted = item.status === 'completed';

    return (
        <div className={`grid grid-cols-[auto_minmax(0,1fr)_auto] items-start gap-2 rounded-xl border p-3 ${isCompleted ? 'border-emerald-200 bg-emerald-50/60' : 'border-black/10 bg-white/40'}`}>
            <Preview
                item={item}
                imageAlt={labels.title}
                videoLabel={labels.videoLabel}
            />
            <div className="min-w-0">
                <p
                    className="truncate text-sm font-medium"
                    style={{ color: palette.deep }}
                    title={item.file.name}
                >
                    {item.file.name}
                </p>
                <div className="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs" style={{ color: palette.dawn }}>
                    <span className="whitespace-nowrap">
                        {formatFileSize(item.file.size)}
                    </span>
                    <span className="whitespace-nowrap">
                        {statusLabels[item.status]}
                    </span>
                </div>
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
                            style={{
                                width: `${item.progress}%`,
                                backgroundColor: palette.celestial,
                            }}
                        />
                    </div>
                )}
            </div>
            <div className="flex shrink-0 items-center justify-end gap-1.5">
                {item.status === 'error' && (
                    <button
                        type="button"
                        onClick={() => onRetry(item.id)}
                        className="cursor-pointer text-xs font-medium whitespace-nowrap underline underline-offset-2"
                        style={{ color: palette.deep }}
                    >
                        {labels.retry}
                    </button>
                )}
                <button
                    type="button"
                    onClick={() => onRemove(item.id)}
                    className="cursor-pointer rounded-full p-1 transition-opacity hover:opacity-70"
                    style={{ color: palette.deep }}
                    aria-label={`${removeLabel}: ${item.file.name}`}
                >
                    <X size={16} />
                </button>
            </div>
            {item.status === 'error' && (
                <p className="col-span-full min-w-0 text-xs break-words text-red-600">
                    {item.error}
                </p>
            )}
        </div>
    );
}
