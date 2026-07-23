import { Head } from '@inertiajs/react';
import MemoryUpcoming from '@/components/memory-wall/MemoryUpcoming';
import MemoryUpload from '@/components/memory-wall/MemoryUpload';
import ShownAfter from '@/components/memory-wall/ShownAfter';
import type { MemoryWallPageProps } from '@/types';
/**
 * Renders the memory wall page.
 */
export default function MemoryWallPage({ wedding, metaData, media, }: MemoryWallPageProps) {
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
            {wedding.is_wedding_date && wedding.is_memory_wall_form_open && (
                <MemoryUpload wedding={wedding} />
            )}

            {/* Show this component after all is finished */}
            {wedding.is_finished && !wedding.is_memory_wall_form_open && (
                <ShownAfter />
            )}
        </>
    );
}
