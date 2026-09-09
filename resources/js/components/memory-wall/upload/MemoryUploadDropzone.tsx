import { Image as ImageIcon } from 'lucide-react';
import FileDropzone from '@/components/FileDropzone';
import type { MemoryWallUploadConfig } from '@/hooks/use-memory-wall-upload';
import { palette } from '../../invitation/theme';
import type { MemoryUploadLabels } from './types';

interface MemoryUploadDropzoneProps {
    config: MemoryWallUploadConfig;
    translations: Pick<
        MemoryUploadLabels,
        'dropzone' | 'browse' | 'dropzoneHint'
    >;
    onFilesSelected: (files: FileList) => void;
}

/** Memory wall presentation of the shared file dropzone. */
export default function MemoryUploadDropzone({
    config,
    translations,
    onFilesSelected,
}: MemoryUploadDropzoneProps) {
    return (
        <FileDropzone
            accept={config.acceptedTypes.join(',')}
            multiple
            onFilesSelected={onFilesSelected}
            className={({ isDragActive }) =>
                `flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed p-6 transition-all hover:opacity-80 ${isDragActive ? 'ring-2 ring-offset-2' : ''}`
            }
            style={({ isDragActive }) => ({
                borderColor: isDragActive
                    ? palette.celestial
                    : 'rgba(67, 58, 102, 0.25)',
                backgroundColor: 'rgba(255, 255, 255, 0.5)',
            })}
        >
            <ImageIcon size={32} style={{ color: palette.deep }} />
            <span className="text-sm font-medium" style={{ color: palette.deep }}>
                {translations.dropzone}
            </span>
            <span className="text-xs" style={{ color: palette.dawn }}>
                {translations.browse} · {translations.dropzoneHint}
            </span>
        </FileDropzone>
    );
}
