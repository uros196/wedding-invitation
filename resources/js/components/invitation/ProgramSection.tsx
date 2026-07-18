import { motion } from 'framer-motion';
import { MapPin, Church, Wine, Heart } from 'lucide-react';
import RingsIcon from './RingsIcon';

const events = [
    {
        icon: RingsIcon,
        title: 'Građansko venčanje',
        time: '13:30',
        venue: 'Opština Valjevo',
        mapUrl: 'https://maps.google.com/?q=Opština+Valjevo',
    },
    {
        icon: Church,
        title: 'Crkveno venčanje',
        time: '14:30',
        venue: 'Hram Pokrova Presvete Bogorodice',
        mapUrl: 'https://maps.google.com/?q=Hram+Pokrova+Presvete+Bogorodice+Valjevo',
    },
    {
        icon: Wine,
        title: 'Svečani ručak',
        time: '16:00',
        venue: 'Vinarija Zorča',
        mapUrl: 'https://maps.google.com/?q=Vinarija+Zorča',
    },
];

export default function ProgramSection() {
    return (
        <div className="w-full bg-[#EEF1F5] relative z-20 px-5  py-10">
            <div className="relative md-auto max-w-sm">
                {/* Vertical center line */}
                <motion.div
                    className="absolute top-4 bottom-4 left-1/2 w-px -translate-x-1/2"
                    style={{ backgroundColor: 'rgba(152, 117, 166, 0.25)' }}
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                    viewport={{ once: true, amount: 0.2 }}
                    transition={{ duration: 1.2, ease: 'easeOut' }}
                />

                {events.map((event, i) => {
                    const Icon = event.icon;
                    return (
                        <div
                            key={i}
                            className="relative mb-12 flex items-baseline last:mb-0"
                        >
                            {/* Left side: time */}
                            <motion.div
                                className="flex w-1/2 flex-col items-end pr-5"
                                initial={{ opacity: 0, x: -50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                viewport={{ once: true, amount: 0.5 }}
                                transition={{
                                    duration: 0.8,
                                    delay: i * 0.15,
                                    ease: 'easeOut',
                                }}
                            > 
                                <p
                                    className="text-2xl font-light sm:text-3xl"
                                    style={{
                                        color: '#0B2F5B',
                                        fontFamily: "'Playfair Display', serif",
                                    }}
                                >
                                    {event.time}
                                </p>
                            </motion.div>

                            {/* Center: heart node */}
                            <motion.div
                                className="absolute top-2 left-1/2 z-10 flex h-7 w-7 -translate-x-1/2 items-center justify-center rounded-full"
                                style={{ backgroundColor: '#EEF1F5' }}
                                initial={{ opacity: 0 }}
                                whileInView={{ opacity: 1 }}
                                viewport={{ once: true, amount: 0.5 }}
                                transition={{
                                    duration: 0.8,
                                    delay: i * 0.15 + 0.3,
                                    ease: 'easeOut',
                                }}
                            >
                                <Heart
                                    size={12}
                                    fill="#9875A6"
                                    style={{ color: '#9875A6' }}
                                />
                            </motion.div>

                            {/* Right side: icon+title inline, venue, map */}
                            <motion.div
                                className="flex w-1/2 flex-col items-start gap-2 pl-4"
                                initial={{ opacity: 0, x: 50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                viewport={{ once: true, amount: 0.5 }}
                                transition={{
                                    duration: 0.8,
                                    delay: i * 0.15 + 0.15,
                                    ease: 'easeOut',
                                }}
                            >
                                <div className="flex items-center gap-1.5">
                                    <Icon
                                        size={20}
                                        strokeWidth={1}
                                        style={{ color: '#433A66' }}
                                    />
                                    <h3
                                        className="text-xl leading-tight font-light whitespace-nowrap sm:text-2xl"
                                        style={{
                                            color: '#433A66',
                                            fontFamily:
                                                "'Great Vibes', cursive",
                                        }}
                                    >
                                        {event.title}
                                    </h3>
                                </div>
                                <p
                                    className="text-xs leading-snug"
                                    style={{ color: '#0B2F5B' }}
                                >
                                    {event.venue}
                                </p>
                                <a
                                    href={event.mapUrl}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="mt-1 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] tracking-widest uppercase transition-all duration-300 hover:scale-105"
                                    style={{
                                        border: '1px solid #0B2F5B',
                                        color: '#0B2F5B',
                                    }}
                                >
                                    <MapPin size={10} />
                                    Mapa
                                </a>
                            </motion.div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
