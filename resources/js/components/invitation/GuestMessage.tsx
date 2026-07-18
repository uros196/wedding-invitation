import { motion } from 'framer-motion';

import { fonts, palette } from './theme';

interface GuestMessageProps {
    title: string;
    message: string;
}

/**
 * A heartfelt note addressed to the guests. Text is customisable but falls
 * back to a warm default when nothing is provided.
 */
export default function GuestMessage({ title, message }: GuestMessageProps) {
    return (
        <div className="relative z-20" style={{ backgroundColor: palette.background }}>
            <motion.div
                className="px-8 py-12 text-center"
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, amount: 0.3 }}
                transition={{ duration: 0.9, ease: 'easeOut' }}
            >
                {title && (
                    <p className="mb-4 text-2xl" style={{ color: palette.dawn, fontFamily: fonts.script }}>
                        {title}
                    </p>
                )}
                <p
                    className="text-sm leading-relaxed font-light sm:text-base"
                    style={{ color: palette.celestial, fontFamily: fonts.serif }}
                >
                    {message}
                </p>
            </motion.div>
        </div>
    );
}
