import { Link } from '@inertiajs/react';
import { Camera, Palette } from 'lucide-react';
import { fonts, palette } from './theme';
import { Wedding } from '@/types';

/**
 * EventMemoriesActive component. Displayed on the day of the event,
 * encouraging guests to share photos and videos they capture from their perspective.
 */
export default function EventMemoriesActive({ wedding }: Wedding) {
    return (
        <div
            className="relative z-20 px-6 py-12"
            style={{
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
                        backgroundColor: palette.background ,
                        color: palette.deep,
                    }}
                >
                    <Camera size={24} />
                </div>

                <h3
                    className="mb-3 text-3xl font-medium tracking-wide"
                    style={{ color: palette.deep }}
                >
                    Naše uspomene
                </h3>

                <p
                    className="mb-4 text-base leading-relaxed"
                    style={{ color: palette.dawn }}
                >
                    Ako ste nas uhvatili fotografijom ili snimkom, podelite ih sa nama putem linka ispod.
                </p>

                <p
                    className="mb-8 text-base font-medium tracking-wide italic"
                    style={{ color: palette.deep }}
                >
                    Jedva čekamo da vidimo venčanje iz vašeg ugla.
                </p>

                <Link
                   
                    className="inline-flex w-full items-center justify-center gap-2 rounded-lg py-4 text-sm tracking-widest uppercase transition-opacity duration-300 hover:opacity-90"
                    style={{
                        backgroundColor: palette.deep,
                        color: palette.background,
                    }}
                >
                    <Camera size={16} />
                    <span>Podeli slike i snimke</span>
                </Link>
            </div>
        </div>
    );
}