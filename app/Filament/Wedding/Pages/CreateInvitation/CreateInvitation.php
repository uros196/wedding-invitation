<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\CreateInvitation;

use App\Enums\Age;
use App\Filament\Wedding\Pages\CreateInvitation\Schemas\GroupForm;
use App\Filament\Wedding\Pages\CreateInvitation\Schemas\GuestForm;
use App\Filament\Wedding\Resources\Groups\GroupResource;
use App\Models\Group;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;

class CreateInvitation extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.wedding.pages.create-invitation';

    public int $step = 1;

    /**
     * @var array<string, mixed>
     */
    public array $groupFormData = [];

    /**
     * @var array<string, mixed>
     */
    public array $guestFormData = [];

    public ?Group $group = null;

    /**
     * Determine whether the page is available to the current user.
     */
    public static function canAccess(): bool
    {
        return auth()->user()->can('access', Group::class);
    }

    /**
     * Get the page title.
     */
    public function getTitle(): string
    {
        return __('wedding.groups.quick_create.title');
    }

    /**
     * Initialize both forms with their simplest defaults.
     */
    public function mount(): void
    {
        $this->getGroupForm()->fill();
        $this->getGuestForm()->fill(['age' => Age::Adult]);
    }

    /**
     * Configure the group form.
     */
    public function groupForm(Schema $schema): Schema
    {
        return GroupForm::configure($schema)->statePath('groupFormData');
    }

    /**
     * Configure the guest form.
     */
    public function guestForm(Schema $schema): Schema
    {
        return GuestForm::configure($schema)->statePath('guestFormData');
    }

    /**
     * Create the group that owns the invitation link.
     */
    public function createGroup(): void
    {
        $data = Arr::only($this->getGroupForm()->getState(), ['name']);
        $group = Group::make($data);

        $this->wedding()->groups()->save($group);
        $this->group = $group;
        $this->group->load('guests');
        $this->step = 2;

        Notification::make()
            ->title(__('wedding.notifications.group_created'))
            ->success()
            ->send();
    }

    /**
     * Add one guest and reset the small form for the next guest.
     */
    public function addGuest(): void
    {
        $group = $this->currentGroup();
        $data = Arr::only($this->getGuestForm()->getState(), ['first_name', 'last_name', 'age']);
        $data['age'] ??= Age::Adult;

        $group->guests()->create($data);
        $this->group = $group->load('guests');
        $this->getGuestForm()->fill(['age' => Age::Adult]);

        Notification::make()
            ->title(__('wedding.notifications.guest_added'))
            ->success()
            ->send();
    }

    /**
     * Start a new group without leaving the guided flow.
     */
    public function createAnotherInvitation(): void
    {
        $this->step = 1;
        $this->group = null;
        $this->getGroupForm()->fill();
        $this->getGuestForm()->fill(['age' => Age::Adult]);
    }

    /**
     * Return to the complete group management table.
     */
    public function finish(): void
    {
        $this->currentGroup();
        $this->redirect(GroupResource::getUrl(), navigate: true);
    }

    /**
     * Resolve the group form schema.
     */
    protected function getGroupForm(): Schema
    {
        $schema = $this->getSchema('groupForm');

        abort_unless($schema instanceof Schema, 500);

        return $schema;
    }

    /**
     * Resolve the guest form schema.
     */
    protected function getGuestForm(): Schema
    {
        $schema = $this->getSchema('guestForm');

        abort_unless($schema instanceof Schema, 500);

        return $schema;
    }

    /**
     * Resolve the wedding belonging to the authenticated user.
     */
    protected function wedding(): Wedding
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $wedding = resolve(WeddingService::class)->getWeddingForUser($user);

        abort_unless($wedding instanceof Wedding, 403);

        return $wedding;
    }

    /**
     * Resolve the current group again within the authenticated wedding.
     */
    protected function currentGroup(): Group
    {
        $group = $this->group;
        $wedding = $this->wedding();
        $currentGroup = $group instanceof Group
            ? $wedding->groups()->find($group->getKey())
            : null;

        abort_unless($currentGroup instanceof Group, 403);

        return $currentGroup->load('guests');
    }
}
