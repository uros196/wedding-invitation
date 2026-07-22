import { motion } from 'framer-motion';
import { Heart, MapPin } from 'lucide-react';

import { getTimelineIcon } from '@/components/icons/timelineIcons';
import type { WeddingTimelineItem } from '@/types';

import { fonts, palette } from './theme';

interface ProgramSectionProps {
    timelines: WeddingTimelineItem[];
}

/**
 * Vertical timeline of the wedding program. Each entry shows the time on one
 * side and the icon, title, address and a map link on the other.
 */
export default function ProgramSection({ timelines }: ProgramSectionProps) {
    if (timelines.length === 0) {
        return null;
    }

    return (
        <div className="relative z-20 w-full px-5 py-10" style={{ backgroundColor: palette.background }}>
            <div className="relative mx-auto max-w-sm">
                {/* Vertical center line */}
                <motion.div
                    className="absolute top-4 bottom-4 left-2/5 w-px -translate-x-1/2"
                    style={{ backgroundColor: 'rgba(152, 117, 166, 0.25)' }}
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                    viewport={{ once: true, amount: 0.2 }}
                    transition={{ duration: 1.2, ease: 'easeOut' }}
                />

                {timelines.map((event, i) => {
                    const Icon = getTimelineIcon(event.icon);

                    return (
                        <div key={i} className="relative mb-12 flex items-baseline last:mb-0">
                            {/* Left side: time */}
                            <motion.div
                                className="flex w-2/5 flex-col items-end pr-5"
                                initial={{ opacity: 0, x: -50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                viewport={{ once: true, amount: 0.5 }}
                                transition={{ duration: 0.8, delay: i * 0.15, ease: 'easeOut' }}
                            >
                                {event.time && (
                                    <p
                                        className="text-xl font-light sm:text-2xl"
                                        style={{ color: palette.deep, fontFamily: fonts.numbers }}
                                    >
                                        {event.time}
                                    </p>
                                )}
                            </motion.div>

                            {/* Center: heart node */}
                            <motion.div
                                className="absolute top-2 left-2/5 z-10 flex h-7 w-7 -translate-x-1/2 items-center justify-center rounded-full"
                                style={{ backgroundColor: palette.background }}
                                initial={{ opacity: 0 }}
                                whileInView={{ opacity: 1 }}
                                viewport={{ once: true, amount: 0.5 }}
                                transition={{ duration: 0.8, delay: i * 0.15 + 0.3, ease: 'easeOut' }}
                            >
                                <Heart size={12} fill={palette.dawn} style={{ color: palette.dawn }} />
                            </motion.div>

                            {/* Right side: icon + title, address, map link */}
                            <motion.div
                                className="flex w-1/2 flex-col items-start gap-2 pl-4"
                                initial={{ opacity: 0, x: 50 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                viewport={{ once: true, amount: 0.5 }}
                                transition={{ duration: 0.8, delay: i * 0.15 + 0.15, ease: 'easeOut' }}
                            >
                                <div className="flex items-center gap-1.5">
                                    {Icon && <Icon size={20} strokeWidth={1} style={{ color: palette.celestial }} />}
                                    <h3
                                        className="text-xl leading-tight font-light whitespace-nowrap sm:text-2xl"
                                        style={{ color: palette.celestial, fontFamily: fonts.script }}
                                    >
                                        {event.title}
                                    </h3>
                                </div>
                                {event.address && (
                                    <p className="text-xs leading-snug" style={{ color: palette.deep }}>
                                        {event.address}
                                    </p>
                                )}
                                {event.map_url && (
                                    <a
                                        href={event.map_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="mt-1 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] tracking-widest uppercase transition-all duration-300 hover:scale-105"
                                        style={{ border: `1px solid ${palette.deep}`, color: palette.deep }}
                                    >
                                        <MapPin size={10} />
                                        Mapa
                                    </a>
                                )}
                            </motion.div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
