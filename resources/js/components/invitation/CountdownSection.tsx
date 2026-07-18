import { useEffect, useMemo, useState } from 'react';

import { fonts, palette } from './theme';

interface TimeLeft {
    days: number;
    hours: number;
    minutes: number;
    seconds: number;
}

interface CountdownSectionProps {
    /** ISO 8601 datetime the countdown counts down to. */
    targetDate: string;
}

function getTimeLeft(target: number): TimeLeft {
    const diff = target - Date.now();

    if (diff <= 0) {
        return { days: 0, hours: 0, minutes: 0, seconds: 0 };
    }

    return {
        days: Math.floor(diff / (1000 * 60 * 60 * 24)),
        hours: Math.floor((diff / (1000 * 60 * 60)) % 24),
        minutes: Math.floor((diff / (1000 * 60)) % 60),
        seconds: Math.floor((diff / 1000) % 60),
    };
}

/**
 * Live countdown to the wedding, rendered as four heart-framed numbers.
 */
export default function CountdownSection({ targetDate }: CountdownSectionProps) {
    const target = useMemo(() => new Date(targetDate).getTime(), [targetDate]);
    const [timeLeft, setTimeLeft] = useState<TimeLeft>(() => getTimeLeft(target));

    useEffect(() => {
        const interval = setInterval(() => setTimeLeft(getTimeLeft(target)), 1000);

        return () => clearInterval(interval);
    }, [target]);

    const units = [
        { value: timeLeft.days, label: 'dana' },
        { value: timeLeft.hours, label: 'sati' },
        { value: timeLeft.minutes, label: 'minuta' },
        { value: timeLeft.seconds, label: 'sekundi' },
    ];

    return (
        <div className="relative z-20 flex w-full justify-center px-6 py-12" style={{ backgroundColor: palette.background }}>
            <div className="flex items-start justify-center gap-2 sm:gap-4">
                {units.map((unit) => (
                    <div key={unit.label} className="relative flex flex-col items-center">
                        <div className="relative flex h-20 w-20 items-center justify-center">
                            <svg
                                className="h-full w-full"
                                viewBox="0 0 24 22"
                                fill="none"
                                stroke={palette.dawn}
                                strokeWidth="0.40"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            >
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                            </svg>
                            <span
                                className="absolute font-light italic"
                                style={{
                                    color: palette.deep,
                                    fontFamily: fonts.serif,
                                    fontSize: '2.25rem',
                                    transform: 'translateY(-3px)',
                                }}
                            >
                                {String(unit.value).padStart(2, '0')}
                            </span>
                        </div>
                        <span className="mt-2 text-[10px] tracking-widest uppercase" style={{ color: palette.dawn }}>
                            {unit.label}
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}
