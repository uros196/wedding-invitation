import type { Media, MetaData } from './invitation';

export interface MemoryWallShare {
    uuid: string;
    title: string;
    expiresAt: string | null;
}

export interface MemoryWallShareMedia extends Media {
    download_url?: string;
}

export interface CursorMediaPage<T> {
    data: T[];
    next_cursor: string | null;
    prev_cursor: string | null;
    [key: string]: unknown;
}

export interface MemoryWallSharePageProps {
    share: MemoryWallShare;
    requiresPassword: boolean;
    allowDownloads: boolean;
    metaData: MetaData;
    media: CursorMediaPage<MemoryWallShareMedia> | null;
}
