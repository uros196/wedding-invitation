import { Image as ImageIcon } from 'lucide-react';
import FileDropzone from '@/components/FileDropzone';
import type { MemoryWallUploadConfig } from '@/hooks/use-memory-wall-upload';
import { palette } from '../../invitation/theme';
import type { MemoryUploadLabels } from './types';

interface MemoryUploadDropzoneProps {
    config: MemoryWallUploadConfig;
    labels: Pick<MemoryUploadLabels, 'dropzone' | 'browse' | 'dropzoneHint'>;
    onFilesSelected: (files: FileList) => void;
}

/** Memory wall presentation of the shared file dropzone. */
export default function MemoryUploadDropzone({
    config,
    labels,
    onFilesSelected,
}: MemoryUploadDropzoneProps) {
    return (
        <FileDropzone
            accept={config.acceptedTypes.join(',')}
            multiple
            onFilesSelected={onFilesSelected}
            className={({ isDragActive }) =>
                `flex min-h-40 cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed p-6 transition-[transform,background-color,border-color,box-shadow] duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] hover:-translate-y-0.5 focus-visible:ring-2 focus-visible:ring-offset-2 ${isDragActive ? 'scale-[1.01] shadow-[0_12px_30px_rgba(67,58,102,0.12)]' : ''}`
            }
            style={({ isDragActive }) => ({
                borderColor: isDragActive
                    ? palette.celestial
                    : 'rgba(67, 58, 102, 0.25)',
                backgroundColor: isDragActive
                    ? 'rgba(255, 255, 255, 0.78)'
                    : 'rgba(255, 255, 255, 0.5)',
            })}
        >
            <ImageIcon size={32} style={{ color: palette.deep }} />
            <span
                className="text-sm font-medium"
                style={{ color: palette.deep }}
            >
                {labels.dropzone}
            </span>
            <span className="text-xs" style={{ color: palette.dawn }}>
                {labels.browse} · {labels.dropzoneHint}
            </span>
        </FileDropzone>
    );
}
