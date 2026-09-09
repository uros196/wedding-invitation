import { ChevronDown } from 'lucide-react';
import { useState } from 'react';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import type { MemoryUploadItem } from '@/hooks/use-memory-wall-upload';
import { palette } from '../../invitation/theme';
import MemoryUploadItemList from './MemoryUploadItemList';
import type { MemoryUploadLabels, MemoryUploadStatusLabels } from './types';

interface MemoryUploadCompletedListProps {
    items: MemoryUploadItem[];
    labels: MemoryUploadLabels;
    statusLabels: MemoryUploadStatusLabels;
    onRetry: (id: string) => void;
    onRemove: (id: string) => void;
}

/** Render completed uploads inside the collapsible summary. */
export default function MemoryUploadCompletedList({
    items,
    labels,
    statusLabels,
    onRetry,
    onRemove,
}: MemoryUploadCompletedListProps) {
    const [isOpen, setIsOpen] = useState(false);

    if (items.length === 0) {
        return null;
    }

    return (
        <Collapsible
            open={isOpen}
            onOpenChange={setIsOpen}
            className="mt-4 text-left"
        >
            <CollapsibleTrigger asChild>
                <button
                    type="button"
                    className="flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl border px-4 py-3 text-sm font-medium transition-opacity hover:opacity-80"
                    style={{
                        color: palette.deep,
                        borderColor: 'rgba(67, 58, 102, 0.15)',
                        backgroundColor: 'rgba(255, 255, 255, 0.3)',
                    }}
                >
                    <span>
                        {labels.completedSummary} ({items.length})
                    </span>
                    <ChevronDown
                        size={16}
                        aria-hidden="true"
                        className={`transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`}
                    />
                </button>
            </CollapsibleTrigger>
            <CollapsibleContent className="mt-3">
                <MemoryUploadItemList
                    items={items}
                    labels={labels}
                    statusLabels={statusLabels}
                    onRetry={onRetry}
                    onRemove={onRemove}
                    variant="completed"
                />
            </CollapsibleContent>
        </Collapsible>
    );
}
