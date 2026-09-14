import { Heart, MessageCircleHeart } from 'lucide-react';
import { fonts, palette } from '../invitation/theme';

/** Explain that the public upload window has already closed. */
export default function ShownAfter() {
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
                className="w-full max-w-md rounded-[2rem] p-2 text-center"
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
                        <Heart
                            size={24}
                            className="fill-current"
                            style={{ color: palette.celestial }}
                        />
                    </div>

                    <h3
                        className="mb-3 text-3xl font-medium tracking-wide"
                        style={{ color: palette.deep }}
                    >
                        Hvala vam!
                    </h3>

                    <p
                        className="mb-6 text-base leading-relaxed"
                        style={{ color: palette.dawn }}
                    >
                        Hvala vam što ste bili deo našeg najlepšeg dana i što
                        ste svojim prisustvom ulepšali svaku uspomenu. Nadamo se
                        da ste uživali isto koliko i mi!
                    </p>

                    <div
                        className="flex items-center justify-center gap-2.5 rounded-2xl p-4 text-xs sm:text-sm"
                        style={{
                            backgroundColor: 'rgba(67, 58, 102, 0.05)',
                            color: palette.deep,
                        }}
                    >
                        <MessageCircleHeart
                            size={18}
                            className="shrink-0"
                            style={{ color: palette.celestial }}
                        />
                        <span>
                            Ukoliko ste zaboravili da pošaljete neku fotografiju
                            ili snimak, slobodno nas kontaktirajte!
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
}
