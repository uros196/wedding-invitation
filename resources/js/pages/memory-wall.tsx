import MemoryUpcoming from '@/components/memory-wall/MemoryUpcoming';
import MemoryUpload from '@/components/memory-wall/MemoryUpload';
import ShownAfter from '@/components/memory-wall/ShownAfter';
import { MemoryWallPageProps } from '@/types';
import { Head } from '@inertiajs/react';
/**
 * Renders the memory wall page.
 */
export default function MemoryWallPage({
    wedding,
    metaData,
    media,
}: MemoryWallPageProps) {

    return (
        <>
            <Head title={metaData.title}>
                <meta name="description" content={metaData.description} />
                <meta property="og:title" content={metaData.title} />
                <meta
                    property="og:description"
                    content={metaData.description}
                />
                <meta property="og:type" content="website" />
                <meta property="og:image" content={metaData.image} />
            </Head>

            {/* Prikazuje se PRE venčanja */}
                <MemoryUpcoming wedding={wedding} />
       

            {/* Prikazuje se NA DAN venčanja i 5 dana posle */}
            <MemoryUpload wedding={wedding} />

            {/* Prikazuje se NAKON 5 dana od venčanja */}
            <ShownAfter />
        </>
    );
}
