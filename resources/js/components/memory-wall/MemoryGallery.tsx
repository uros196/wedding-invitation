import { useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog';
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
    const [selectedImage, setSelectedImage] = useState<Media | null>(null);

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
                        {media.map((item) =>
                            item.mime_type.startsWith('video/') ? (
                                <a
                                    key={item.uuid}
                                    href={item.original_url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="group relative aspect-square cursor-pointer overflow-hidden rounded-xl bg-black/5 shadow-sm"
                                >
                                    {/* Video thumbnails are optional; the native player
                                        keeps the original upload playable without ffmpeg. */}
                                    <video
                                        src={item.original_url}
                                        muted
                                        playsInline
                                        preload="metadata"
                                        controls
                                        aria-label={videoLabel}
                                        className="h-full w-full object-cover"
                                    />
                                </a>
                            ) : (
                                <button
                                    key={item.uuid}
                                    type="button"
                                    aria-label={imageAlt}
                                    onClick={() => setSelectedImage(item)}
                                    className="group relative aspect-square cursor-pointer overflow-hidden rounded-xl bg-black/5 shadow-sm"
                                >
                                    <img
                                        src={item.preview_url || item.original_url}
                                        alt={imageAlt}
                                        loading="lazy"
                                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    />
                                </button>
                            ),
                        )}
                    </div>
                )}

                {selectedImage && (
                    <Dialog
                        open
                        onOpenChange={(open) => {
                            if (!open) {
                                setSelectedImage(null);
                            }
                        }}
                    >
                        <DialogContent className="flex max-h-[calc(100vh-2rem)] max-w-[calc(100%-1rem)] items-center justify-center border-0 bg-black/95 p-2 sm:max-w-6xl">
                            <DialogTitle className="sr-only">
                                {imageAlt}
                            </DialogTitle>
                            <DialogDescription className="sr-only">
                                {selectedImage.file_name}
                            </DialogDescription>
                            <img
                                src={selectedImage.original_url}
                                alt={imageAlt}
                                className="max-h-[calc(100vh-3rem)] max-w-full object-contain"
                            />
                        </DialogContent>
                    </Dialog>
                )}
            </div>
        </section>
    );
}
