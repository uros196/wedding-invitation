import { useState } from 'react';
import { useForm, Link } from '@inertiajs/react';
import { Upload, ArrowLeft, Image as ImageIcon, Loader2, CheckCircle2 } from 'lucide-react';
import { fonts, palette } from '../invitation/theme';
import { Wedding } from '@/types';

interface MemoryUploadProps {
    wedding: Wedding;
}

export default function MemoryUpload({wedding}: MemoryUploadProps) {
    const { data, setData, post, processing, progress, errors, reset } = useForm({
        media: [] as File[],
    });

    const [successMessage, setSuccessMessage] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // post(route('memory-wall.upload', myroute), {
        //     forceFormData: true,
        //     onSuccess: () => {
        //         reset('media');
        //         setSuccessMessage(true);
        //         setTimeout(() => setSuccessMessage(false), 5000);
        //     },
        // });
    };

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
                {/* Ikonica na vrhu */}
                <div
                    className="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-5"
                    style={{
                        backgroundColor: 'rgba(67, 58, 102, 0.08)',
                        color: palette.deep,
                    }}
                >
                    <Upload size={24} style={{ color: palette.celestial }} />
                </div>

                <h3
                    className="mb-3 text-3xl font-medium tracking-wide"
                    style={{ color: palette.deep }}
                >
                    Naše uspomene
                </h3>

                <p
                    className="mb-6 text-base leading-relaxed"
                    style={{ color: palette.dawn }}
                >
                    Dobrodošli na naš najlepši dan! Podelite sa nama fotografije i snimke koje ste zabeležili.
                </p>

                <form onSubmit={handleSubmit} className="space-y-4 mb-6">
                    {/* Naša custom zona za izbor fajlova */}
                    <label
                        className="border-2 border-dashed rounded-xl p-6 flex flex-col items-center justify-center gap-2 cursor-pointer transition-all hover:opacity-80 block"
                        style={{
                            borderColor: 'rgba(67, 58, 102, 0.25)',
                            backgroundColor: 'rgba(255, 255, 255, 0.5)',
                        }}
                    >
                        <input
                            type="file"
                            multiple
                            accept="image/*,video/*"
                            className="hidden"
                            onChange={(e) => {
                                if (e.target.files) {
                                    setData('media', Array.from(e.target.files));
                                }
                            }}
                        />
                        <ImageIcon size={32} style={{ color: palette.deep }} />
                        <span className="text-sm font-medium" style={{ color: palette.deep }}>
                            {data.media.length > 0
                                ? `Izabrano fajlova: ${data.media.length}`
                                : 'Kliknite da izaberete slike i snimke'}
                        </span>
                        <span className="text-xs" style={{ color: palette.dawn }}>
                            Možete izabrati više fajlova odjednom
                        </span>
                    </label>

                    {errors.media && (
                        <p className="text-xs text-red-500 mt-1">{errors.media}</p>
                    )}

                    {/* Progress bar za praćenje uploada */}
                    {progress && (
                        <div className="w-full bg-white/50 rounded-full h-2 overflow-hidden border border-[rgba(67,58,102,0.1)]">
                            <div
                                className="h-full transition-all duration-300"
                                style={{
                                    width: `${progress.percentage}%`,
                                    backgroundColor: palette.deep,
                                }}
                            ></div>
                        </div>
                    )}

                    {/* Poruka o uspehu */}
                    {successMessage && (
                        <div className="rounded-xl p-3 text-xs flex items-center justify-center gap-2 bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <CheckCircle2 size={16} />
                            <span>Uspesno ste poslali uspomene! Hvala vam!</span>
                        </div>
                    )}

                    {/* Dugme za slanje se pojavljuje tek kada se izaberu fajlovi */}
                    {data.media.length > 0 && (
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-3 rounded-xl text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2 shadow-sm cursor-pointer disabled:opacity-50"
                            style={{
                                backgroundColor: palette.deep,
                                color: palette.background,
                            }}
                        >
                            {processing ? (
                                <>
                                    <Loader2 size={16} className="animate-spin" />
                                    <span>Slanje u toku...</span>
                                </>
                            ) : (
                                <span>Pošalji uspomene</span>
                            )}
                        </button>
                    )}
                </form>

            
            </div>
        </div>
    );
}