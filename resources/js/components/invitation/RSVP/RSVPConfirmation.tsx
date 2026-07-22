import { Heart } from 'lucide-react';
import { fonts, palette } from './../theme';

export default function RSVPConfirmation() {
    return (
        <div
            className="relative z-20 flex w-full flex-col items-center gap-4 px-0 py-12 text-center sm:px-6"
            style={{
                backgroundColor: palette.background,
                fontFamily: fonts.serif,
            }}
        >
            <Heart size={28} style={{ color: palette.celestial }} />
            <p
                className="text-2xl font-medium tracking-wide italic"
                style={{ color: palette.celestial }}
            >
                Hvala na potvrdi!
            </p>
        </div>
    );
}
