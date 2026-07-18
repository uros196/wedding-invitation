import { icons } from 'lucide-react';
import type { LucideProps } from 'lucide-react';
import type { ComponentType } from 'react';

import RingsIcon from './RingsIcon';

/**
 * A component able to render a timeline icon.
 *
 * Both Lucide icons and the local custom icons (e.g. RingsIcon) are supported,
 * so the props type is kept intentionally permissive.
 */
export type TimelineIconComponent = ComponentType<
    Partial<LucideProps> & {
        size?: number;
        strokeWidth?: number;
        style?: React.CSSProperties;
        className?: string;
    }
>;

/**
 * Custom, project-specific icons that are not part of the Lucide set.
 * Keys must match the icon name provided by the backend.
 */
const customIcons: Record<string, TimelineIconComponent> = {
    rings: RingsIcon,
};

/**
 * Resolve an icon name (already converted to the `lucide-react` PascalCase name
 * by the backend) to a renderable React component. Falls back to `null` when
 * the icon cannot be found.
 */
export function getTimelineIcon(name?: string | null): TimelineIconComponent | null {
    if (!name) {
        return null;
    }

    if (customIcons[name]) {
        return customIcons[name];
    }

    const lucideIcons = icons as Record<string, TimelineIconComponent>;

    return lucideIcons[name] ?? null;
}
