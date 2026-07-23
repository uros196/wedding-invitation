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
    uuid: string;
    bride_name: string;
    groom_name: string;
    hero_image: string;
    /** Localized day name (e.g. `subota`). */
    wedding_day: string;
    /** Formatted wedding date (e.g. `10.09.2026`). */
    wedding_date: string;
    /** Flag that marks whether the wedding is coming. */
    is_wedding_coming: boolean;
    /** Flag that marks whether the wedding is today. */
    is_wedding_date: boolean;
    /** Flag that marks whether the wedding is finished. */
    is_finished: boolean;
    /** Formatted RSVP deadline (e.g. `01.09.2026 20:00`). */
    rsvp_deadline: string;
    /** Whether RSVPs are still open. */
    is_rsvp_open: boolean;
    /** ISO 8601 datetime used by the countdown. */
    countdown_due_datetime: string;
    /** Rich-text (HTML) welcome message. */
    welcome_text: string;
    timelines_count: number | null;
    timelines: WeddingTimelineItem[];
    /** Memory wall related data */
    has_memory_wall: boolean;
    is_memory_wall_form_open: boolean;
    is_memory_wall_finished: boolean;
}

/**
 * Represents a media object with details about its file properties and metadata.
 */
export interface Media {
    id: number,
    uuid: string,
    name: string,
    file_name: string,
    mime_type: string,
    type: string,
    extension: string,
    human_readable_size: string,
    preview_url: string,
    original_url: string,
    size: number,
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
    has_single_guest: boolean;
    guests: Guest[];
}

/**
 * Open Graph metadata used for link previews.
 */
export interface MetaData {
    title: string;
    description: string;
    image: string;
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

export interface MemoryWallPageProps {
    wedding: Wedding;
    metaData: MetaData;
    media: Media[];
}
