import { motion } from 'framer-motion';

export default function GuestMessage() {
    return (
        <div className="relative z-20 bg-[#EEF1F5]">
            <motion.div
                className="px-8 py-12 text-center"
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, amount: 0.3 }}
                transition={{ duration: 0.9, ease: 'easeOut' }}
            >
                <p
                    className="mb-4 text-2xl"
                    style={{
                        color: '#9875A6',
                        fontFamily: "'Great Vibes', cursive",
                    }}
                >
                    Dragim gostima
                </p>
                <p
                    className="text-sm leading-relaxed font-light sm:text-base"
                    style={{
                        color: '#433A66',
                        fontFamily: "'Playfair Display', serif",
                    }}
                >
                    Svaki ljubavni put je jedinstven, a naš je vodio do ovog
                    trenutka. Vaše prisustvo čini naš dan potpunijim i lepšim.
                    Hvala vam što ste deo naše priče, što ste tu da podelite
                    radost, suze i osmehe sa nama. Ovo je početak jednog novog
                    poglavlja, a vi ste nam dragocena podrška na tom putu.
                </p>
            </motion.div>
        </div>
    );
}
