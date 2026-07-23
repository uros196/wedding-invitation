import { Heart, MessageCircleHeart } from 'lucide-react';
import { fonts, palette } from '../invitation/theme';

export default function ShownAfter() {
    return (
        <div
            className="min-h-screen w-full flex flex-col items-center justify-start px-4 pt-16 sm:pt-24 pb-12"
            style={{
                backgroundColor: palette.background,
                fontFamily: fonts.serif,
            }}
        >
            <div
                className="w-full max-w-md rounded-2xl p-6 sm:p-8 text-center shadow-sm"
                style={{
                    backgroundColor: 'rgba(255, 255, 255, 0.3)',
                    border: '1px solid rgba(67, 58, 102, 0.15)',
                }}
            >
                {/* Ikonica umesto obične tačke - može simbola srca ili mlade i mladoženje */}
                <div
                    className="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-5"
                    style={{
                        backgroundColor: 'rgba(67, 58, 102, 0.08)',
                        color: palette.deep,
                    }}
                >
                    <Heart size={24} className="fill-current" style={{ color: palette.celestial }} />
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
                    Hvala vam što ste bili deo našeg najlepšeg dana i što ste svojim prisustvom ulepšali svaku uspomenu. Nadamo se da ste uživali isto koliko i mi!
                </p>

                <div
                    className="rounded-xl p-4 text-xs sm:text-sm flex items-center justify-center gap-2.5"
                    style={{
                        backgroundColor: 'rgba(67, 58, 102, 0.05)',
                        color: palette.deep,
                    }}
                >
                    <MessageCircleHeart size={18} className="flex-shrink-0" style={{ color: palette.celestial }} />
                    <span>Ukoliko ste zaboravili da pošaljete neku fotografiju ili snimak, slobodno nas kontaktirajte!</span>
                </div>
            </div>
        </div>
    );
}