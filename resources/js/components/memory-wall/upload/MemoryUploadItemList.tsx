import { AnimatePresence, motion } from 'framer-motion';
import type { MemoryUploadItem } from '@/hooks/use-memory-wall-upload';
import { palette } from '../../invitation/theme';
import MemoryUploadItemRow from './MemoryUploadItemRow';
import type { MemoryUploadLabels, MemoryUploadStatusLabels } from './types';

interface MemoryUploadItemListProps {
    items: MemoryUploadItem[];
    labels: MemoryUploadLabels;
    statusLabels: MemoryUploadStatusLabels;
    onRetry: (id: string) => void;
    onRemove: (id: string) => void;
    variant?: 'active' | 'completed';
}

/** Render upload rows with the animation used by the relevant queue. */
export default function MemoryUploadItemList({
    items,
    labels,
    statusLabels,
    onRetry,
    onRemove,
    variant = 'active',
}: MemoryUploadItemListProps) {
    const isCompletedList = variant === 'completed';

    return (
        <div
            className="space-y-3 text-left"
            aria-live={isCompletedList ? undefined : 'polite'}
        >
            {!isCompletedList && items.length > 0 && (
                <p
                    className="text-sm font-medium"
                    style={{ color: palette.deep }}
                >
                    {items.length} {labels.selected}
                </p>
            )}
            <AnimatePresence initial={isCompletedList}>
                {items.map((item) => (
                    <motion.div
                        key={item.id}
                        layout
                        initial={{ opacity: 0, y: -8 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{
                            opacity: 0,
                            y: -8,
                            transition: {
                                duration: isCompletedList ? 0.25 : 0.35,
                                ease: 'easeOut',
                            },
                        }}
                        transition={{
                            delay: isCompletedList ? 0.35 : 0,
                            duration: isCompletedList ? 0.3 : 0.25,
                            ease: 'easeOut',
                        }}
                    >
                        <MemoryUploadItemRow
                            item={item}
                            labels={labels}
                            statusLabels={statusLabels}
                            onRetry={onRetry}
                            onRemove={onRemove}
                        />
                    </motion.div>
                ))}
            </AnimatePresence>
        </div>
    );
}
