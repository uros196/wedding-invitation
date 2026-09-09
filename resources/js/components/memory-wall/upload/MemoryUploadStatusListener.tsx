import { useEchoPublic } from '@laravel/echo-react';
import type { MemoryWallUploadProcessedEvent } from '@/hooks/use-memory-wall-upload';

interface MemoryUploadStatusListenerProps {
    weddingUuid: string;
    onProcessed: (event: MemoryWallUploadProcessedEvent) => void;
}

/** Listen for queued completion results for all uploads in one wedding. */
export default function MemoryUploadStatusListener({ weddingUuid, onProcessed }: MemoryUploadStatusListenerProps) {
    useEchoPublic<MemoryWallUploadProcessedEvent>(
        `memory-wall.${weddingUuid}`,
        '.memoryWallUploadProcessed',
        onProcessed,
        [onProcessed],
    );

    return null;
}
