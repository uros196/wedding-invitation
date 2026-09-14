import type { MemoryWallUploadConfig } from '@/hooks/use-memory-wall-upload';
import type { MemoryUploadLabels } from './upload/types';

function formatFileSize(bytes: number): string {
    const units = ['B', 'KB', 'MB', 'GB'];
    const unitIndex = Math.min(
        Math.floor(Math.log(Math.max(bytes, 1)) / Math.log(1024)),
        units.length - 1,
    );
    const value = bytes / 1024 ** unitIndex;
    const formattedValue = Number.isInteger(value)
        ? value.toString()
        : value.toFixed(1);

    return `${formattedValue} ${units[unitIndex]}`;
}

export const memoryWallCopy = {
    upload: {
        title: 'Podelite uspomenu',
        description:
            'Pošaljite fotografije i snimke sa proslave. Uspomene će se pojaviti na zidu čim budu obrađene.',
        dropzone: 'Prevucite fotografije ili snimke ovde',
        browse: 'Izaberite fajlove',
        dropzoneHint: 'JPG, PNG, HEIC, MP4, MOV i drugi podržani formati',
        videoLabel: 'Snimak',
        selected: 'izabrano',
        uploadAction: 'Pošaljite fajlove',
        uploading: 'Slanje je u toku',
        processing: 'Obrada je u toku',
        queued: 'Čeka na slanje',
        completed: 'Poslato',
        failed: 'Slanje nije uspelo',
        retry: 'Pokušajte ponovo',
        cancel: 'Otkaži',
        remove: 'Ukloni',
        maxFiles: (count: number): string =>
            `Odjednom možete izabrati najviše ${count} fajlova.`,
        maxFileSize: (bytes: number): string =>
            `Svaki fajl može imati do ${formatFileSize(bytes)}.`,
        fileTypeError: 'Ovaj tip fajla nije podržan.',
        fileSizeError: 'Veličina fajla prelazi dozvoljeni limit.',
        maxFilesError: (count: number): string =>
            `Možete izabrati najviše ${count} fajlova.`,
        networkError:
            'Slanje nije završeno. Proverite internet vezu i pokušajte ponovo.',
        completedSummary: 'Poslate uspomene',
        autoUploadHint: 'Slanje počinje odmah nakon izbora fajlova.',
        manualUploadHint:
            'Pregledajte izabrane fajlove, pa pokrenite slanje kada budete spremni.',
        previewLoading: 'Učitavanje pregleda',
    },
    gallery: {
        title: 'Mali prikaz uspomena',
        empty: 'Budite prvi koji će podeliti uspomenu.',
        imageAlt: 'Podeljena uspomena',
        videoLabel: 'Snimak',
    },
} as const;

export function createMemoryUploadLabels(
    config: Pick<MemoryWallUploadConfig, 'maxFiles' | 'maxFileSize'>,
): MemoryUploadLabels {
    return {
        title: memoryWallCopy.upload.title,
        description: memoryWallCopy.upload.description,
        dropzone: memoryWallCopy.upload.dropzone,
        browse: memoryWallCopy.upload.browse,
        dropzoneHint: memoryWallCopy.upload.dropzoneHint,
        videoLabel: memoryWallCopy.upload.videoLabel,
        selected: memoryWallCopy.upload.selected,
        uploadAction: memoryWallCopy.upload.uploadAction,
        uploading: memoryWallCopy.upload.uploading,
        processing: memoryWallCopy.upload.processing,
        queued: memoryWallCopy.upload.queued,
        completed: memoryWallCopy.upload.completed,
        failed: memoryWallCopy.upload.failed,
        retry: memoryWallCopy.upload.retry,
        cancel: memoryWallCopy.upload.cancel,
        remove: memoryWallCopy.upload.remove,
        maxFiles: memoryWallCopy.upload.maxFiles(config.maxFiles),
        maxFileSize: memoryWallCopy.upload.maxFileSize(config.maxFileSize),
        fileTypeError: memoryWallCopy.upload.fileTypeError,
        fileSizeError: memoryWallCopy.upload.fileSizeError,
        maxFilesError: memoryWallCopy.upload.maxFilesError(config.maxFiles),
        networkError: memoryWallCopy.upload.networkError,
        completedSummary: memoryWallCopy.upload.completedSummary,
        previewLoading: memoryWallCopy.upload.previewLoading,
    };
}
