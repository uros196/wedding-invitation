import { useEffect, useRef, useState } from 'react';
import type { MemoryUploadItem } from '@/hooks/use-memory-wall-upload';

const DEFAULT_DISPLAY_DURATION = 2000;

interface DelayedCompletedItems {
    activeItems: MemoryUploadItem[];
    completedItems: MemoryUploadItem[];
}

/** Keep newly completed uploads visible before moving them to the summary. */
export function useDelayedCompletedItems(
    items: MemoryUploadItem[],
    displayDuration = DEFAULT_DISPLAY_DURATION,
): DelayedCompletedItems {
    const handledCompletedItemIds = useRef(new Set<string>());
    const completedItemTimers = useRef(
        new Map<string, ReturnType<typeof setTimeout>>(),
    );
    const [recentlyCompletedItemIds, setRecentlyCompletedItemIds] = useState(
        new Set<string>(),
    );

    useEffect(() => {
        const completedItemIds = new Set(
            items
                .filter((item) => item.status === 'completed')
                .map((item) => item.id),
        );

        completedItemIds.forEach((itemId) => {
            if (handledCompletedItemIds.current.has(itemId)) {
                return;
            }

            handledCompletedItemIds.current.add(itemId);

            setRecentlyCompletedItemIds((currentIds) => {
                if (currentIds.has(itemId)) {
                    return currentIds;
                }

                return new Set(currentIds).add(itemId);
            });

            const timer = setTimeout(() => {
                setRecentlyCompletedItemIds((currentIds) => {
                    if (!currentIds.has(itemId)) {
                        return currentIds;
                    }

                    const nextIds = new Set(currentIds);
                    nextIds.delete(itemId);

                    return nextIds;
                });
                completedItemTimers.current.delete(itemId);
            }, displayDuration);

            completedItemTimers.current.set(itemId, timer);
        });

        items.forEach((item) => {
            if (item.status !== 'completed') {
                handledCompletedItemIds.current.delete(item.id);
            }
        });

        completedItemTimers.current.forEach((timer, itemId) => {
            if (completedItemIds.has(itemId)) {
                return;
            }

            clearTimeout(timer);
            completedItemTimers.current.delete(itemId);
            setRecentlyCompletedItemIds((currentIds) => {
                if (!currentIds.has(itemId)) {
                    return currentIds;
                }

                const nextIds = new Set(currentIds);
                nextIds.delete(itemId);

                return nextIds;
            });
        });
    }, [displayDuration, items]);

    useEffect(() => {
        const timers = completedItemTimers.current;

        return () => {
            timers.forEach((timer) => clearTimeout(timer));
            timers.clear();
        };
    }, []);

    return {
        activeItems: items.filter(
            (item) =>
                item.status !== 'completed' ||
                recentlyCompletedItemIds.has(item.id),
        ),
        completedItems: items.filter(
            (item) =>
                item.status === 'completed' &&
                !recentlyCompletedItemIds.has(item.id),
        ),
    };
}
