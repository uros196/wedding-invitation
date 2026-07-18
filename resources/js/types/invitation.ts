/**
 * Type definitions describing the data the server sends to the invitation page.
 *
 * The backend is the single source of truth: every field here maps directly to
 * a Laravel API Resource (`WeddingResource`, `GroupResource`, `GuestResource`,
 * `WeddingTimelineResource`, `MetaDataResource`).
 */

/**
 * A single entry of the wedding program (e.g. civil ceremony, church, dinner).
 */
export interface WeddingTimelineItem {
    title: string;
    address: string | null;
    /** Already formatted as `H:i` by the backend. */
    time: string | null;
    map_url: string | null;
    /**
     * Icon name resolved by {@link getTimelineIcon}. Either a `lucide-react`
     * PascalCase name (e.g. `AlarmClock`) or a custom icon key (e.g. `rings`).
     */
    icon: string | null;
}

/**
 * The wedding details shared across the whole invitation.
 */
export interface Wedding {
    bride_name: string;
    groom_name: string;
    hero_image: string;
    /** Localized day name (e.g. `subota`). */
    wedding_day: string;
    /** Formatted wedding date (e.g. `19.09.2026`). */
    wedding_date: string;
    /** Formatted RSVP deadline (e.g. `01.09.2026 20:00`). */
    rsvp_deadline: string;
    /** ISO 8601 datetime used by the countdown. */
    countdown_due_datetime: string;
    /** Rich-text (HTML) welcome message. */
    welcome_text: string;
    timelines_count: number | null;
    timelines: WeddingTimelineItem[];
}

/**
 * A single guest belonging to the invited group.
 */
export interface Guest {
    id: number;
    full_name: string;
    is_accepted: boolean;
}

/**
 * The invited group (a household / couple / family sharing one invitation).
 */
export interface Group {
    id: number;
    uuid: string;
    invitation_title: string | null;
    invitation_message: string | null;
    has_plus_one: boolean;
    guests_count: number | null;
    guests: Guest[];
}

/**
 * Open Graph metadata used for link previews.
 */
export interface MetaData {
    title: string;
    description: string;
    image: string | null;
}

/**
 * Props received by the invitation page from the `GroupController::show`.
 */
export interface InvitationPageProps {
    wedding: Wedding;
    group: Group;
    metaData: MetaData;
    [key: string]: unknown;
}
