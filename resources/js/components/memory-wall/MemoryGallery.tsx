import type { Media } from '@/types';

/** Text and media data required by the public memory wall gallery. */
interface MemoryGalleryProps {
    media: Media[];
    title: string;
    empty: string;
    imageAlt: string;
    videoLabel: string;
}

/**
 * Render a lightweight random preview of completed memory wall media.
 */
export default function MemoryGallery({ media, title, empty, imageAlt, videoLabel }: MemoryGalleryProps) {
    return (
        <section className="w-full px-4 pb-16" aria-labelledby="memory-wall-gallery-title">
            <div className="mx-auto w-full max-w-5xl">
                <h2 id="memory-wall-gallery-title" className="mb-6 text-center text-2xl font-medium" style={{ color: '#433a66' }}>
                    {title}
                </h2>

                {media.length === 0 ? (
                    <p className="text-center text-sm" style={{ color: '#8b83a8' }}>
                        {empty}
                    </p>
                ) : (
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                        {media.map((item) => (
                            <a
                                key={item.uuid}
                                href={item.original_url}
                                target="_blank"
                                rel="noreferrer"
                                className="group relative aspect-square overflow-hidden rounded-xl bg-black/5 shadow-sm"
                            >
                                {item.mime_type.startsWith('video/') ? (
                                    // Video thumbnails are optional; the native player
                                    // keeps the original upload playable without ffmpeg.
                                    <video
                                        src={item.original_url}
                                        muted
                                        playsInline
                                        preload="metadata"
                                        controls
                                        aria-label={videoLabel}
                                        className="h-full w-full object-cover"
                                    />
                                ) : (
                                    <img
                                        src={item.preview_url || item.original_url}
                                        alt={imageAlt}
                                        loading="lazy"
                                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    />
                                )}
                            </a>
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}