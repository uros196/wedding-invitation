import { Head } from '@inertiajs/react';
import { motion } from 'framer-motion';

import HeroSection from '@/components/invitation/HeroSection';
import ProgramSection from '@/components/invitation/ProgramSection';
import CountdownSection from '@/components/invitation/CountdownSection';
import RSVPForm from '@/components/invitation/RSVPForm';
import GuestMessage from '@/components/invitation/GuestMessage';
import WelcomeMessage from '@/components/invitation/WelcomeMessage';

export default function Welcome() {
    return (
        <>
            <Head title="Jelena & Uroš - Venčanje" />

            <div
                className="flex min-h-screen w-full flex-col items-center"

                style={{ backgroundColor: '#EEF1F5' }}
            >
                {/* Ograničena širina na mobilnim uređajima i centrirano */}
                <div className="mx-auto w-full max-w-lg">
                    <HeroSection />

                    <WelcomeMessage />

                    <ProgramSection />

                    <CountdownSection />

                    <GuestMessage />

                    <RSVPForm />

                    {/*  Footer */}
                    <div className="px-6 py-10 text-center bg-[#EEF1F5] relative z-20">
                        <p
                            className="text-3xl font-light"
                            style={{
                                color: '#433A66',
                                fontFamily: "'Great Vibes', cursive",
                            }}
                        >
                            Jelena & Uroš
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}

// import { Head, Link, usePage } from '@inertiajs/react';
// // import { dashboard } from '@/routes';

// export default function Welcome() {
//     const { auth } = usePage().props;

//     return (
//         <>
//             <Head title="Welcome" />
//             <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">

//             </div>
//         </>
//     );
// }
