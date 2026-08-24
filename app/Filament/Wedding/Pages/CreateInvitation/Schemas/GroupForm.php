<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\CreateInvitation\Schemas;

use App\Filament\Wedding\Resources\Groups\Schemas\Components\Form\NameInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Form schema for the first step of invitation creation.
 */
class GroupForm
{
    /**
     * Configure the minimal group form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('wedding.groups.quick_create.group.heading'))
                ->description(__('wedding.groups.quick_create.group.description'))
                ->schema([
                    NameInput::make(),
                ]),
        ]);
    }
}
