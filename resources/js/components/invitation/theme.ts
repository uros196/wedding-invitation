/**
 * Shared colour palette and font families for the invitation.
 *
 * Centralised here so every section stays visually consistent and a future
 * re-theme only needs to touch a single file.
 */
export const palette = {
    /** Page and card background. */
    background: '#EEF1F5',
    /** Deep navy used for dates and primary actions. */
    deep: '#0B2F5B',
    /** Muted violet used for the main body text and headings. */
    celestial: '#433A66',
    /** Soft mauve used for accents and secondary labels. */
    dawn: '#9875A6',
} as const;

export const fonts = {
    /** Calligraphic display font for names and titles. */
    script: "'Great Vibes', cursive",
    
    /** Elegant serif for body copy and dates. */
    serif: "'Instrument Serif', serif",
    numbers: "'Lora', serif",
} as const;
