import { Heart, Camera } from 'lucide-react';
import { fonts, palette } from './../theme';

interface RSVPConfirmationProps {
    hasMemoryWall: boolean;
}

export default function RSVPConfirmation({ hasMemoryWall }: RSVPConfirmationProps) {
    return (
        <div
            className="relative z-20 flex w-full flex-col items-center gap-4 px-4 py-12 text-center sm:px-6"
            style={{
                backgroundColor: palette.background,
                fontFamily: fonts.serif,
            }}
        >
            <Heart
                size={30}
                style={{ color: palette.celestial }}
                className="fill-current"
            />

            <p
                className="text-xl font-medium tracking-wide italic sm:text-2xl"
                style={{ color: palette.celestial }}
            >
                Hvala na potvrdi!
            </p>

            <p
                className="max-w-lg text-base font-medium tracking-wide italic sm:text-xl"
                style={{ color: palette.dawn }}
            >
                Radujemo se vašem dolasku i jedva čekamo da proslavimo ovaj
                poseban dan sa vama.
            </p>

            {hasMemoryWall && (
                <div className="max-w-lg text-center">
                    <p
                        className="inline text-sm font-medium italic sm:text-base"
                        style={{ color: palette.celestial }}
                    >
                        Slobodno sačuvajte ovu stranu! Tog dana, ovde vas čeka
                        galerija i mesto gde ćete moći da podelite sa nama sve
                        uspomene koje zabeležite{' '}
                    </p>
                    <Camera
                        size={16}
                        style={{ color: palette.celestial }}
                        className="mb-1 ml-1 inline align-middle"
                    />
                </div>
            )}
        </div>
    );
}
