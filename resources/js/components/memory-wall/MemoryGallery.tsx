import { Fancybox } from '@fancyapps/ui';
import '@fancyapps/ui/dist/fancybox/fancybox.css';
import { useEffect, useRef } from 'react';

import type { Media } from '@/types';
import { fonts, palette } from '../invitation/theme';
import { memoryWallCopy } from './copy';

/** Text and media data required by the public memory wall gallery. */
interface MemoryGalleryProps {
    media: Media[];
}

/**
 * Render a lightweight random preview of completed memory wall media.
 */
export default function MemoryGallery({ media }: MemoryGalleryProps) {
    const galleryRef = useRef<HTMLElement | null>(null);

    useEffect(() => {
        const gallery = galleryRef.current;

        if (!gallery) {
            return;
        }

        Fancybox.bind(gallery, '[data-fancybox="memory-wall-gallery"]', {
            Carousel: {
                infinite: false,
            },
        });

        return () => {
            Fancybox.unbind(gallery);
            Fancybox.close();
        };
    }, []);

    return (
        <section
            ref={galleryRef}
            className="w-full px-4 pt-4 pb-20"
            style={{
                backgroundColor: palette.background,
                fontFamily: fonts.serif,
            }}
            aria-labelledby="memory-wall-gallery-title"
        >
            <div className="mx-auto w-full max-w-5xl">
                <h2
                    id="memory-wall-gallery-title"
                    className="mb-7 text-center text-3xl font-medium tracking-wide"
                    style={{ color: palette.deep }}
                >
                    {memoryWallCopy.gallery.title}
                </h2>

                {media.length === 0 ? (
                    <p
                        className="mx-auto max-w-sm rounded-2xl border border-dashed px-6 py-8 text-center text-sm"
                        style={{
                            color: palette.dawn,
                            borderColor: 'rgba(67, 58, 102, 0.2)',
                            backgroundColor: 'rgba(255, 255, 255, 0.22)',
                        }}
                    >
                        {memoryWallCopy.gallery.empty}
                    </p>
                ) : (
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 md:grid-cols-4">
                        {media.map((item) =>
                            item.mime_type.startsWith('video/') ? (
                                <a
                                    key={item.uuid}
                                    href={item.original_url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="group relative aspect-square cursor-pointer overflow-hidden rounded-2xl bg-black/5 shadow-[0_12px_30px_rgba(67,58,102,0.1)] ring-1 ring-black/5 transition-[transform,box-shadow] duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] hover:-translate-y-1 hover:shadow-[0_18px_36px_rgba(67,58,102,0.16)]"
                                >
                                    {/* Video thumbnails are optional; the native player
                                        keeps the original upload playable without ffmpeg. */}
                                    <video
                                        src={item.original_url}
                                        muted
                                        playsInline
                                        preload="metadata"
                                        controls
                                        aria-label={
                                            memoryWallCopy.gallery.videoLabel
                                        }
                                        className="h-full w-full object-cover"
                                    />
                                </a>
                            ) : (
                                <a
                                    key={item.uuid}
                                    href={item.original_url}
                                    data-fancybox="memory-wall-gallery"
                                    data-caption={item.name}
                                    aria-label={memoryWallCopy.gallery.imageAlt}
                                    className="group relative aspect-square cursor-pointer overflow-hidden rounded-2xl bg-black/5 shadow-[0_12px_30px_rgba(67,58,102,0.1)] ring-1 ring-black/5 transition-[transform,box-shadow] duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] hover:-translate-y-1 hover:shadow-[0_18px_36px_rgba(67,58,102,0.16)]"
                                >
                                    <img
                                        src={
                                            item.preview_url ||
                                            item.original_url
                                        }
                                        alt={memoryWallCopy.gallery.imageAlt}
                                        loading="lazy"
                                        className="h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.32,0.72,0,1)] group-hover:scale-105"
                                    />
                                </a>
                            ),
                        )}
                    </div>
                )}
            </div>
        </section>
    );
}
