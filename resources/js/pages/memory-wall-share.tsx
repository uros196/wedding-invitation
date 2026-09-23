import { Form, Head, InfiniteScroll } from '@inertiajs/react';
import { LockKeyhole } from 'lucide-react';
import { useRef } from 'react';
import type { ComponentRef } from 'react';

import { unlock } from '@/actions/App/Http/Controllers/MemoryWallShareController';
import { fonts, palette } from '@/components/invitation/theme';
import { memoryWallCopy } from '@/components/memory-wall/copy';
import MemoryGallery from '@/components/memory-wall/MemoryGallery';
import type { MemoryWallSharePageProps } from '@/types';

/**
 * Render a public, live Memory Wall share page.
 */
export default function MemoryWallSharePage({
    share,
    requiresPassword,
    allowDownloads,
    metaData,
    media,
}: MemoryWallSharePageProps) {
    const infiniteScrollRef = useRef<ComponentRef<typeof InfiniteScroll>>(null);

    if (requiresPassword || media === null) {
        return (
            <>
                <Head title={share.title} />
                <Form action={unlock.url(share.uuid)} method="post">
                    {({ errors, processing }) => (
                        <main
                            className="flex min-h-screen items-center justify-center px-6 py-12"
                            style={{
                                backgroundColor: palette.background,
                                fontFamily: fonts.serif,
                            }}
                        >
                            <section
                                className="w-full max-w-md rounded-3xl border p-8 text-center shadow-[0_20px_60px_rgba(67,58,102,0.12)] sm:p-10"
                                style={{
                                    borderColor: 'rgba(67, 58, 102, 0.15)',
                                    backgroundColor:
                                        'rgba(255, 255, 255, 0.42)',
                                }}
                                aria-labelledby="memory-wall-share-title"
                            >
                                <LockKeyhole
                                    className="mx-auto mb-5 h-10 w-10"
                                    style={{ color: palette.dawn }}
                                    aria-hidden="true"
                                />
                                <h1
                                    id="memory-wall-share-title"
                                    className="mb-3 text-4xl font-medium tracking-wide"
                                    style={{ color: palette.deep }}
                                >
                                    {share.title}
                                </h1>
                                <h2
                                    className="mb-2 text-2xl font-medium"
                                    style={{ color: palette.celestial }}
                                >
                                    {memoryWallCopy.share.passwordTitle}
                                </h2>
                                <p
                                    className="mb-8 text-sm"
                                    style={{ color: palette.dawn }}
                                >
                                    {memoryWallCopy.share.passwordDescription}
                                </p>

                                <div className="flex flex-col gap-3 text-left">
                                    <label
                                        htmlFor="memory-wall-share-password"
                                        className="text-sm font-medium"
                                        style={{ color: palette.celestial }}
                                    >
                                        {memoryWallCopy.share.passwordLabel}
                                    </label>
                                    <input
                                        id="memory-wall-share-password"
                                        name="password"
                                        type="password"
                                        placeholder={
                                            memoryWallCopy.share
                                                .passwordPlaceholder
                                        }
                                        autoComplete="current-password"
                                        required
                                        className="rounded-xl border bg-white/70 px-4 py-3 transition outline-none focus:ring-2"
                                        style={{
                                            borderColor:
                                                'rgba(67, 58, 102, 0.2)',
                                            color: palette.celestial,
                                        }}
                                        aria-invalid={Boolean(errors.password)}
                                        aria-describedby={
                                            errors.password
                                                ? 'memory-wall-share-password-error'
                                                : undefined
                                        }
                                    />
                                    {errors.password && (
                                        <p
                                            id="memory-wall-share-password-error"
                                            className="text-sm text-red-700"
                                            role="alert"
                                        >
                                            {errors.password}
                                        </p>
                                    )}
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="mt-3 rounded-xl px-5 py-3 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                                        style={{
                                            backgroundColor: palette.deep,
                                        }}
                                    >
                                        {processing
                                            ? memoryWallCopy.share.unlocking
                                            : memoryWallCopy.share.unlock}
                                    </button>
                                </div>
                            </section>
                        </main>
                    )}
                </Form>
            </>
        );
    }

    const mediaItems = media.data.filter(
        (item, index, items) =>
            items.findIndex((candidate) => candidate.uuid === item.uuid) ===
            index,
    );

    return (
        <>
            <Head title={share.title}>
                <meta name="description" content={metaData.description} />
                <meta property="og:title" content={share.title} />
                <meta
                    property="og:description"
                    content={metaData.description}
                />
                <meta property="og:type" content="website" />
                <meta property="og:image" content={metaData.image} />
            </Head>
            <main
                className="min-h-screen"
                style={{
                    backgroundColor: palette.background,
                    fontFamily: fonts.serif,
                }}
            >
                <header className="mx-auto flex w-full max-w-5xl flex-col gap-2 px-6 pt-12 pb-2 text-center">
                    <h1
                        className="text-5xl font-medium tracking-wide"
                        style={{ color: palette.deep }}
                    >
                        {share.title}
                    </h1>
                </header>
                <InfiniteScroll
                    ref={infiniteScrollRef}
                    data="media"
                    buffer={500}
                    as="div"
                    itemsElement="#memory-wall-share-grid"
                    loading={({ loadingNext }) =>
                        loadingNext ? (
                            <p
                                className="mx-auto max-w-sm px-6 pb-12 text-center text-sm"
                                style={{ color: palette.dawn }}
                                role="status"
                            >
                                {memoryWallCopy.gallery.loading}
                            </p>
                        ) : null
                    }
                >
                    {({ loadingNext }) => (
                        <MemoryGallery
                            media={mediaItems}
                            allowDownloads={allowDownloads}
                            onNeedMore={() => {
                                const infiniteScroll =
                                    infiniteScrollRef.current;

                                if (
                                    !loadingNext &&
                                    infiniteScroll?.hasNext()
                                ) {
                                    infiniteScroll.fetchNext();
                                }
                            }}
                        />
                    )}
                </InfiniteScroll>
            </main>
        </>
    );
}
