<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Actions;

use App\Models\Group;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class ShareGroupAction
{
    /**
     * Create the share group action.
     */
    public static function make(): Action
    {
        return Action::make('share')
            ->label(__('messages.share'))
            ->icon(Heroicon::Share)
            ->color('gray')
            ->alpineClickHandler(fn (Group $record) => "
                (async () => {
                    const url = '{$record->url}';
                    const title = '".addslashes($record->name)."';

                    if (navigator.share) {
                        try {
                            await navigator.share({
                                title: title,
                                url: url
                            });
                            return;
                        } catch (error) {
                            if (error.name !== 'AbortError') {
                                console.error('Error sharing:', error);
                            } else {
                                return;
                            }
                        }
                    }

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        try {
                            await navigator.clipboard.writeText(url);
                            \$tooltip('".addslashes(__('messages.link_copied'))."');
                            return;
                        } catch (error) {
                            console.error('Clipboard error:', error);
                        }
                    }

                    const textArea = document.createElement('textarea');
                    textArea.value = url;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-9999px';
                    textArea.style.top = '0';
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        \$tooltip('".addslashes(__('messages.link_copied'))."');
                    } catch (error) {
                        console.error('Fallback copy error:', error);
                    }
                    document.body.removeChild(textArea);
                })()
            ");
    }
}
