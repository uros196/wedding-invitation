import { Calendar, Camera } from 'lucide-react';
import { fonts, palette } from '../invitation/theme'; 

interface MemoryUpcomingProps {
    eventDate: string; // npr. "19. septembar 2026."
}

export default function MemoryUpcoming({ eventDate }: MemoryUpcomingProps) {
    return (
        <div
className="min-h-screen w-full flex flex-col items-center justify-start px-4 pt-16 sm:pt-24 pb-12"            style={{
                backgroundColor: palette.background,
                fontFamily: fonts.serif,
            }}
        >
            <div
                className="mx-auto max-w-md rounded-2xl p-6 sm:p-8 text-center"
                style={{
                    backgroundColor: 'rgba(255, 255, 255, 0.3)',
                    border: '1px solid rgba(67, 58, 102, 0.15)',
                }}
            >
                <div
                    className="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4"
                    style={{
                        backgroundColor: 'rgba(67, 58, 102, 0.08)',
                        color: palette.deep,
                    }}
                >
                    <Camera size={24} />
                </div>

                <h3
                    className="mb-2 text-3xl font-medium tracking-wide"
                    style={{ color: palette.deep }}
                >
                    Uskoro: Naše uspomene
                </h3>

                <p
                    className="mb-6 text-base leading-relaxed"
                    style={{ color: palette.dawn }}
                >
                    Radujemo se danu koji je pred nama! Dok iščekujemo proslavu zakazanu za{' '}
                    <span className="font-medium" style={{ color: palette.deep }}>
                        {eventDate}
                    </span>
                    , ovde vredno spremamo mesto gde ćemo nakon svega zajedno sabrati sve uspomene i fotografije.
                </p>

                <div
                    className="rounded-xl p-4 text-xs sm:text-sm flex items-center justify-center gap-2"
                    style={{
                        backgroundColor: 'rgba(67, 58, 102, 0.05)',
                        color: palette.deep,
                    }}
                >
                    <Calendar size={16} className="flex-shrink-0" style={{ color: palette.celestial }} />
                    <span>Stranica će se aktivirati na dan venčanja!</span>
                </div>
            </div>
        </div>
    );
}