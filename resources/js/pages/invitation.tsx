import { Head } from '@inertiajs/react';

import CountdownSection from '@/components/invitation/CountdownSection';
import GuestMessage from '@/components/invitation/GuestMessage';
import HeroSection from '@/components/invitation/HeroSection';
import ProgramSection from '@/components/invitation/ProgramSection';
import RSVPForm from '@/components/invitation/RSVP/RSVPForm';
import { fonts, palette } from '@/components/invitation/theme';
import WelcomeMessage from '@/components/invitation/WelcomeMessage';
import type { InvitationPageProps } from '@/types';

/**
 * The dynamic wedding invitation. Every piece of content is provided by the
 * server (`GroupController::show`); this page only composes the sections.
 */
export default function Invitation({ wedding, group, metaData }: InvitationPageProps) {
    return (
        <>
            <Head title={metaData.title}>
                <meta name="description" content={metaData.description} />
                <meta property="og:title" content={metaData.title} />
                <meta property="og:description" content={metaData.description} />
                <meta property="og:type" content="website" />
                <meta property="og:image" content={metaData.image ?? wedding.hero_image} />
            </Head>

            <div className="flex min-h-screen w-full flex-col items-center"
                style={{ backgroundColor: palette.background }}
            >
                <div className="mx-auto w-full max-w-lg">
                    <HeroSection
                        brideName={wedding.bride_name}
                        groomName={wedding.groom_name}
                        imageUrl={wedding.hero_image}
                    />

                    <WelcomeMessage
                        welcomeText={wedding.welcome_text}
                        weddingDay={wedding.wedding_day}
                        weddingDate={wedding.wedding_date}
                    />

                    <ProgramSection timelines={wedding.timelines} />

                    <CountdownSection targetDate={wedding.countdown_due_datetime} />

                    {group.invitation_title && group.invitation_message && (
                        <GuestMessage
                            title={group.invitation_title}
                            message={group.invitation_message}
                        />
                    )}

                    {wedding.is_rsvp_open && (
                        <RSVPForm
                            group={group}
                            rsvpDeadline={wedding.rsvp_deadline}
                        />
                    )}

                    {/* Footer */}
                    <div
                        className="relative z-20 px-6 py-10 text-center"
                        style={{ backgroundColor: palette.background }}
                    >
                        <p
                            className="text-3xl font-light"
                            style={{
                                color: palette.celestial,
                                fontFamily: fonts.script,
                            }}
                        >
                            {wedding.bride_name} &amp; {wedding.groom_name}
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
