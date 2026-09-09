import type { MemoryUploadItem, MemoryWallUploadConfig } from '@/hooks/use-memory-wall-upload';
import type { Media, Wedding } from '@/types';

export interface MemoryUploadLabels {
    title: string;
    description: string;
    dropzone: string;
    browse: string;
    dropzoneHint: string;
    videoLabel: string;
    selected: string;
    uploadAction: string;
    uploading: string;
    processing: string;
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
}

export type MemoryUploadStatusLabels = Record<
    MemoryUploadItem['status'],
    string
>;

export interface MemoryUploadProps {
    wedding: Wedding;
    config: MemoryWallUploadConfig;
    translations: MemoryUploadLabels;
    onMediaUploaded: (media: Media) => void;
}
