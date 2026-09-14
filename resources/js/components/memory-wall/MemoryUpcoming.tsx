import { Calendar, Camera } from 'lucide-react';
import { fonts, palette } from '../invitation/theme';

/** Wedding date displayed before the memory wall upload window opens. */
interface MemoryUpcomingProps {
    weddingDate: string;
}

/** Explain when the memory wall becomes available to guests. */
export default function MemoryUpcoming({ weddingDate }: MemoryUpcomingProps) {
    return (
        <div
            className="flex min-h-[100dvh] w-full flex-col items-center justify-start px-4 pt-14 pb-12 sm:pt-20"
            style={{
                backgroundColor: palette.background,
                backgroundImage:
                    'radial-gradient(circle at 15% 15%, rgba(152, 117, 166, 0.12), transparent 34%), radial-gradient(circle at 85% 0%, rgba(11, 47, 91, 0.08), transparent 30%)',
                fontFamily: fonts.serif,
            }}
        >
            <div
                className="mx-auto w-full max-w-md rounded-[2rem] p-2 text-center"
                style={{
                    backgroundColor: 'rgba(255, 255, 255, 0.22)',
                    border: '1px solid rgba(255, 255, 255, 0.55)',
                    boxShadow: '0 24px 70px rgba(67, 58, 102, 0.12)',
                }}
            >
                <div
                    className="rounded-[1.5rem] p-6 sm:p-8"
                    style={{
                        backgroundColor: 'rgba(255, 255, 255, 0.34)',
                        border: '1px solid rgba(67, 58, 102, 0.12)',
                    }}
                >
                    <div
                        className="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-full"
                        style={{
                            backgroundColor: 'rgba(67, 58, 102, 0.08)',
                            color: palette.deep,
                        }}
                    >
                        <Camera size={24} />
                    </div>

                    <h3
                        className="mb-3 text-3xl font-medium tracking-wide"
                        style={{ color: palette.deep }}
                    >
                        Uskoro: Naše uspomene
                    </h3>

                    <p
                        className="mb-6 text-base leading-relaxed"
                        style={{ color: palette.dawn }}
                    >
                        Radujemo se danu koji je pred nama! Dok iščekujemo
                        proslavu zakazanu za{' '}
                        <span
                            className="font-medium"
                            style={{ color: palette.deep }}
                        >
                            {weddingDate}
                        </span>
                        , ovde vredno spremamo mesto gde ćemo nakon svega
                        zajedno sabrati sve uspomene i fotografije.
                    </p>

                    <div
                        className="flex items-center justify-center gap-2 rounded-2xl p-5 text-xs sm:text-sm"
                        style={{
                            backgroundColor: 'rgba(67, 58, 102, 0.05)',
                            color: palette.deep,
                        }}
                    >
                        <Calendar
                            size={16}
                            className="shrink-0"
                            style={{ color: palette.celestial }}
                        />
                        <span>Stranica će se aktivirati na dan venčanja!</span>
                    </div>
                </div>
            </div>
        </div>
    );
}
