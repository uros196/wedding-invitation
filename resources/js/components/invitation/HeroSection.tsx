import { motion } from 'framer-motion';

import { fonts, palette } from './theme';

/** Fallback image shown when the wedding has no hero image uploaded yet. */
const FALLBACK_IMAGE =
    'https://media.base44.com/images/public/user_6a54f975f3bff6d3fb6d1618/ca08165d7_20250520_2037593.jpg';

interface HeroSectionProps {
    brideName: string;
    groomName: string;
    imageUrl?: string | null;
}

/**
 * Full-width hero image with the couple's names layered on top.
 * The image stays sticky so the following sections scroll over it.
 */
export default function HeroSection({ brideName, groomName, imageUrl }: HeroSectionProps) {
    return (
        <div className="sticky top-0 left-0 z-0 w-full">
            <motion.img
                src={imageUrl || FALLBACK_IMAGE}
                alt={`${brideName} i ${groomName}`}
                className="h-auto w-full object-cover"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 1.6, ease: 'easeOut' }}
            />

            {/* Names layered over the image */}
            <div className="absolute top-0 right-0 left-0 flex flex-col items-center pt-4 sm:pt-6">
                <motion.h1
                    className="text-5xl drop-shadow-lg sm:text-6xl md:text-7xl"
                    style={{ fontFamily: fonts.script, color: palette.celestial }}
                    initial={{ opacity: 0, x: -80 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 1, duration: 1.2, ease: 'easeOut' }}
                >
                    {brideName}
                </motion.h1>

                <motion.span
                    className="text-2xl tracking-widest sm:text-3xl"
                    style={{ color: palette.celestial, fontFamily: fonts.script }}
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 1.8, duration: 0.8, ease: 'easeOut' }}
                >
                    &
                </motion.span>

                <motion.h1
                    className="text-5xl drop-shadow-lg sm:text-6xl md:text-7xl"
                    style={{ fontFamily: fonts.script, color: palette.celestial }}
                    initial={{ opacity: 0, x: 80 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 1.1, duration: 1.2, ease: 'easeOut' }}
                >
                    {groomName}
                </motion.h1>
            </div>
        </div>
    );
}
