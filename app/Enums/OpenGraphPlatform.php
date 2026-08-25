<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Supported chat applications for the Open Graph preview.
 */
enum OpenGraphPlatform: string
{
    case WhatsApp = 'whatsapp';
    case Viber = 'viber';
    case Messenger = 'messenger';
    case Telegram = 'telegram';

    /**
     * Normalize platform configuration while preserving the configured order.
     *
     * A null value means that every supported platform should be displayed.
     * Unknown values are ignored so a stale configuration cannot break the
     * whole preview.
     *
     * @param  array<int, self|string>|null  $platforms
     * @return array<int, self>
     */
    public static function normalize(?array $platforms): array
    {
        if ($platforms === null) {
            return self::cases();
        }

        $normalized = [];

        foreach ($platforms as $platform) {
            $resolvedPlatform = match (true) {
                $platform instanceof self => $platform,
                is_string($platform) => self::tryFrom($platform),
                default => null,
            };

            if ($resolvedPlatform === null || in_array($resolvedPlatform, $normalized, true)) {
                continue;
            }

            $normalized[] = $resolvedPlatform;
        }

        return $normalized;
    }

    /**
     * Get the translated label used by the preview tabs.
     */
    public function label(): string
    {
        return (string) __('wedding.manage_wedding.meta.preview.platforms.'.$this->value.'.label');
    }

    /**
     * Get the anonymous Blade component used to render the platform card.
     */
    public function viewComponent(): string
    {
        return 'open-graph-preview.'.$this->value;
    }

    /**
     * Get the stable tab identifier for the platform.
     */
    public function tabId(): string
    {
        return 'open-graph-preview-tab-'.$this->value;
    }

    /**
     * Get the stable panel identifier for the platform.
     */
    public function panelId(): string
    {
        return 'open-graph-preview-panel-'.$this->value;
    }
}
