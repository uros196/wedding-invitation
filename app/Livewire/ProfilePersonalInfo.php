<?php

declare(strict_types=1);

namespace App\Livewire;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;

class ProfilePersonalInfo extends PersonalInfo
{
    /** @var array<int, string> */
    public array $only = ['name', 'locale'];

    /**
     * Get the profile fields retained from the previous profile settings page.
     *
     * @return array<int, Field>
     */
    protected function getProfileFormComponents(): array
    {
        return [
            $this->getNameComponent(),
            Select::make('locale')
                ->label(__('Language'))
                ->options([
                    'sr_Latn' => __('Srpski'),
                    'en' => __('English'),
                ])
                ->default(config('app.locale'))
                ->required(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return parent::form($schema)->model($this->user);
    }

    /**
     * Handle form submission.
     */
    public function submit(): void
    {
        $locale = $this->user->getAttributeValue('locale');

        $this->form->saveRelationships();
        parent::submit();

        // Refresh topbar for immediate effect
        $this->dispatch('refresh-topbar');

        // If user locale has changed, reload the page
        if ($locale !== $this->user->getAttributeValue('locale')) {
            redirect(request()->header('referer'));
        }
    }
}
