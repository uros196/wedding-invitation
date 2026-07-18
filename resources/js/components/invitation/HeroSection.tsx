import { motion } from 'framer-motion';

export default function HeroSection() {
    return (
        <div className="sticky top-0 left-0 z-0 w-full">
            <motion.img
                src="https://media.base44.com/images/public/user_6a54f975f3bff6d3fb6d1618/ca08165d7_20250520_2037593.jpg"
                alt="Jelena i Uroš"

                className="h-auto w-full object-cover"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 1.6, ease: 'easeOut' }}
            />

            {/* Kontejner sa imenima postavljen preko slike */}
            <div className="absolute top-0 right-0 left-0 flex flex-col items-center pt-4 sm:pt-6">
                {/* Ime Jelena ulazi sa leve strane */}
                <motion.h1
                    className="text-4xl drop-shadow-lg sm:text-5xl md:text-6xl"
                    style={{
                        fontFamily: "'Great Vibes', cursive",
                        color: '#433A66',
                    }}
                    initial={{ opacity: 0, x: -80 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 1, duration: 1.2, ease: 'easeOut' }}
                >
                    Jelena
                </motion.h1>

                {/* Znak & se blago pojavljuje u sredini */}
                <motion.span
                    className="text-2xl tracking-widest sm:text-3xl"
                    style={{
                        color: '#433A66',
                        fontFamily: "'Great Vibes', cursive",
                    }}
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 1.8, duration: 0.8, ease: 'easeOut' }}
                >
                    &
                </motion.span>

                {/* Ime Uroš ulazi sa desne strane */}
                <motion.h1
                    className="text-4xl drop-shadow-lg sm:text-5xl md:text-6xl"
                    style={{
                        fontFamily: "'Great Vibes', cursive",
                        color: '#433A66',
                    }}
                    initial={{ opacity: 0, x: 80 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 1.1, duration: 1.2, ease: 'easeOut' }}
                >
                    Uroš
                </motion.h1>
            </div>
        </div>
    );
}
