<?php

declare(strict_types=1);

namespace App\Livewire;

use Filament\Schemas\Schema;
use Jeffgreco13\FilamentBreezy\Livewire\UpdatePassword;

class ProfileUpdatePassword extends UpdatePassword
{
    /**
     * Use the same components as the parent but with revealable option.
     */
    public function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);

        // For every password field, make it revealable
        $components = collect($schema->getComponents())
            ->each(fn ($component) => $component->revealable());

        // Set new modified components
        return $schema->components($components->all());
    }
}
