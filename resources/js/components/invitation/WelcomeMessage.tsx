import { motion } from 'framer-motion';

const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            delayChildren: 1.3, 
            staggerChildren: 0.03,
        },
    },
};

const letterVariants = {
    hidden: { opacity: 0 },
    visible: { opacity: 1 },
};
export default function WelcomeMessage() {
    return (
        <>
            <div
                className="relative z-30 -mt-[20px] h-[30px] w-full"
                style={{
                    background:
                        'radial-gradient(circle at 25px -10px, transparent 28px, #EEF1F5 29px)',
                    backgroundSize: '50px 100%',
                    backgroundRepeat: 'repeat-x',
                    backgroundPosition: 'top',
                }}
            />
            <div className="relative z-20 w-full overflow-visible bg-[#EEF1F5] pb-10">
                <motion.div
                    className="px-8 pt-10 pb-6 text-center"
                    variants={containerVariants}
                    initial="hidden"
                    whileInView="visible"
                    viewport={{ once: true, amount: 0.3 }}
                >
                    <p
                        className="text-base leading-relaxed font-light italic sm:text-lg"
                        style={{
                            color: '#433A66',
                            fontFamily: "'Playfair Display', serif",
                        }}
                    >
                        {'Dragi naši, s ljubavlju vas pozivamo da budete deo našeg venčanja.'
                            .split('')
                            .map((char, index) => (
                                <motion.span
                                    key={index}
                                    variants={letterVariants}
                                >
                                    {char}
                                </motion.span>
                            ))}
                    </p>
                </motion.div>
                {/* Animirani datum */}
                <motion.div
                    className="px-6 pb-2 text-center"
                    initial={{ opacity: 0, x: 60 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    viewport={{ once: true, amount: 0.3 }}
                    transition={{ duration: 0.8, ease: 'easeOut' }}
                >
                    <p
                        className="text-sm tracking-[0.3em] uppercase"
                        style={{ color: '#9875A6' }}
                    >
                        Subota
                    </p>
                    <p
                        className="mt-1 text-3xl sm:text-5xl"
                        style={{
                            color: '#0B2F5B',
                            fontFamily: "'Playfair Display', serif",
                            fontWeight: 100,
                        }}
                    >
                        19. 09. 2026. 
                        {/* ovde dodati datum iz back-a  */}
                    </p>
                </motion.div>
            </div>
        </>
    );
}
