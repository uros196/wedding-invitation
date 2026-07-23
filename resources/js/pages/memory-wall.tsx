import MemoryUpcoming from '@/components/memory-wall/MemoryUpcoming';
import { MemoryWallPageProps } from '@/types';
import { Head } from '@inertiajs/react';
/**
 * Renders the memory wall page.
 */
export default function MemoryWallPage({ wedding, metaData, media }: MemoryWallPageProps) {
    return (
        <>
            <Head title={metaData.title}>
                <meta name="description" content={metaData.description} />
                <meta property="og:title" content={metaData.title} />
                <meta property="og:description" content={metaData.description} />
                <meta property="og:type" content="website" />
                <meta property="og:image" content={metaData.image} />
            </Head>
            <MemoryUpcoming eventDate={wedding.wedding_date} ></MemoryUpcoming>
        </>
    );
}
