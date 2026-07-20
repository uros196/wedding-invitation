import { motion } from 'framer-motion';

import { fonts, palette } from './theme';

interface WelcomeMessageProps {
    /** Rich-text (HTML) welcome message coming from the backend. */
    welcomeText?: string | null;
    weddingDay: string;
    weddingDate: string;
}

/**
 * The introductory greeting and the wedding date, sitting on the perforated
 * edge that reveals the sticky hero image above it.
 */
export default function WelcomeMessage({ welcomeText, weddingDay, weddingDate }: WelcomeMessageProps) {
    return (
        <>
            {/* Perforated (ticket-like) top edge */}
            <div
                className="relative z-30 -mt-[20px] h-[30px] w-full"
                style={{
                    background: `radial-gradient(circle at 25px -10px, transparent 28px, ${palette.background} 29px)`,
                    backgroundSize: '50px 100%',
                    backgroundRepeat: 'repeat-x',
                    backgroundPosition: 'top',
                }}
            />
            <div className="relative z-20 w-full overflow-visible pb-10" style={{ backgroundColor: palette.background }}>
                {welcomeText && (
                    <motion.div
                        className="px-8 pt-10 pb-6 text-center text-base leading-relaxed font-light italic sm:text-lg [&_p]:m-0"
                        style={{ color: palette.celestial, fontFamily: fonts.serif }}
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        viewport={{ once: true, amount: 0.3 }}
                        transition={{ duration: 1, ease: 'easeOut' }}
                        dangerouslySetInnerHTML={{ __html: welcomeText }}
                    />
                )}

                {/* Wedding date */}
                <motion.div
                    className="px-6 pb-2 text-center"
                    initial={{ opacity: 0, x: 60 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    viewport={{ once: true, amount: 0.3 }}
                    transition={{ duration: 0.8, ease: 'easeOut' }}
                >
                    <p className="text-sm tracking-[0.3em] uppercase" style={{ color: palette.dawn }}>
                        {weddingDay}
                    </p>
                    <p
                        className="mt-1 text-3xl sm:text-5xl"
                        style={{ color: palette.deep, fontFamily: fonts.numbers, fontWeight: 100 }}
                    >
                        {weddingDate}
                    </p>
                </motion.div>
            </div>
        </>
    );
}
