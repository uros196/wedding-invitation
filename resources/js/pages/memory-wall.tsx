import { Head } from '@inertiajs/react';
import { useCallback, useState } from 'react';
import MemoryGallery from '@/components/memory-wall/MemoryGallery';
import MemoryUpcoming from '@/components/memory-wall/MemoryUpcoming';
import MemoryUpload from '@/components/memory-wall/MemoryUpload';
import ShownAfter from '@/components/memory-wall/ShownAfter';
import type { MemoryWallPageProps } from '@/types';

/**
 * Renders the memory wall page.
 */
export default function MemoryWallPage({ wedding, metaData, media, uploadConfig, translations }: MemoryWallPageProps) {
    // Keep server-provided media and newly completed uploads in one gallery list.
    const [visibleMedia, setVisibleMedia] = useState(media);

    /** Insert new uploads first while preventing duplicate media entries. */
    const handleMediaUploaded = useCallback((newMedia: (typeof media)[number]): void => {
        setVisibleMedia((currentMedia) => [newMedia, ...currentMedia.filter((item) => item.uuid !== newMedia.uuid)]);
    }, []);

    return (
        <>
            <Head title={metaData.title}>
                <meta name="description" content={metaData.description} />
                <meta property="og:title" content={metaData.title} />
                <meta property="og:description" content={metaData.description} />
                <meta property="og:type" content="website" />
                <meta property="og:image" content={metaData.image} />
            </Head>

            {/* Show this component before the wedding */}
            {wedding.is_wedding_coming && (
                <MemoryUpcoming weddingDate={wedding.wedding_date} />
            )}

            {/* Show this component on the wedding day and so long as the Memory Wall form is open */}
            {wedding.is_memory_wall_form_open && (
                <MemoryUpload
                    wedding={wedding}
                    config={uploadConfig}
                    translations={translations.upload}
                    onMediaUploaded={handleMediaUploaded}
                />
            )}

            {!wedding.is_wedding_coming && (
                <MemoryGallery
                    media={visibleMedia}
                    title={translations.gallery.title}
                    empty={translations.gallery.empty}
                    imageAlt={translations.gallery.imageAlt}
                    videoLabel={translations.gallery.videoLabel}
                />
            )}

            {/* Show this component after all is finished */}
            {wedding.is_finished && !wedding.is_memory_wall_form_open && (
                <ShownAfter />
            )}
        </>
    );
}
