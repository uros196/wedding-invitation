import { Head } from '@inertiajs/react';

import CountdownSection from '@/components/invitation/CountdownSection';
import GuestMessage from '@/components/invitation/GuestMessage';
import HeroSection from '@/components/invitation/HeroSection';
import ProgramSection from '@/components/invitation/ProgramSection';
import RSVPForm from '@/components/invitation/RSVP/RSVPForm';
import { fonts, palette } from '@/components/invitation/theme';
import WelcomeMessage from '@/components/invitation/WelcomeMessage';
import type { Group, WeddingTimelineItem } from '@/types';

/**
 * Static design reference for the invitation. It feeds the shared invitation
 * components with hardcoded sample data so the layout can be previewed without
 * a backend. The live, data-driven page lives in `pages/invitation.tsx`.
 */

const SAMPLE_TIMELINES: WeddingTimelineItem[] = [
    {
        title: 'Građansko venčanje',
        time: '13:30',
        address: 'Opština Valjevo',
        map_url: 'https://maps.google.com/?q=Opština+Valjevo',
        icon: 'rings',
    },
    {
        title: 'Crkveno venčanje',
        time: '14:30',
        address: 'Hram Pokrova Presvete Bogorodice',
        map_url:
            'https://maps.google.com/?q=Hram+Pokrova+Presvete+Bogorodice+Valjevo',
        icon: 'Church',
    },
    {
        title: 'Svečani ručak',
        time: '16:00',
        address: 'Vinarija Zorča',
        map_url: 'https://maps.google.com/?q=Vinarija+Zorča',
        icon: 'Wine',
    },
];

const SAMPLE_GROUP: Group = {
    id: 0,
    uuid: 'preview',
    description: null,
    has_plus_one: true,
    guests_count: 2,
    guests: [
        { id: 1, full_name: 'Jelena Jovanović', is_accepted: false },
        { id: 2, full_name: 'Uroš Pantelić', is_accepted: false },
    ],
};

export default function Welcome() {
    return (
        <>
            <Head title="Jelena & Uroš - Venčanje" />

            <div
                className="flex min-h-screen w-full flex-col items-center"
                style={{ backgroundColor: palette.background }}
            >
                <div className="mx-auto w-full max-w-lg">
                    <HeroSection brideName="Jelena" groomName="Uroš" />

                    <WelcomeMessage
                        welcomeText="<p>Dragi naši, s ljubavlju vas pozivamo da budete deo našeg venčanja.</p>"
                        weddingDay="Subota"
                        weddingDate="19. 09. 2026."
                    />

                    <ProgramSection timelines={SAMPLE_TIMELINES} />

                    <CountdownSection targetDate="2026-09-19T13:30:00" />

                    <GuestMessage />

                    <RSVPForm
                        group={SAMPLE_GROUP}
                        rsvpDeadline="01. septembra 2026"
                    />

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
                            Jelena &amp; Uroš
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
