import { Heart, Camera } from 'lucide-react';
import { fonts, palette } from './../theme';

export default function RSVPConfirmation() {
    return (
        <div
    className="relative z-20 flex w-full flex-col items-center gap-4 px-4 py-12 text-center sm:px-6"
    style={{
        backgroundColor: palette.background,
        fontFamily: fonts.serif,
    }}
>
    <Heart size={30} style={{ color: palette.celestial }} className="fill-current" />
    
    <p
        className="text-xl sm:text-2xl font-medium tracking-wide italic"
        style={{ color: palette.celestial }}
    >
        Hvala na potvrdi!
    </p>

    <p
        className="text-base sm:text-xl font-medium tracking-wide italic max-w-lg"
        style={{ color: palette.dawn }}
    >
        Radujemo se vašem dolasku i jedva čekamo da proslavimo ovaj poseban dan sa vama.
    </p>

    <div className="text-center max-w-lg">
        <p
            className="inline text-sm sm:text-base font-medium italic"
            style={{ color: palette.celestial }}
        >
            Slobodno sačuvajte ovu stranu! Tog dana, ovde vas čeka galerija i mesto gde ćete moći da podelite sa nama sve uspomene koje zabeležite{' '}
        </p>
        <Camera
            size={16}
            style={{ color: palette.celestial }}
            className="mb-1 inline ml-1 align-middle"
        />
    </div>
</div>
    );
}
