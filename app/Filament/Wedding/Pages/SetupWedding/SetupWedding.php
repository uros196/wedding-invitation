<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\SetupWedding;

use App\Filament\Wedding\Pages\Dashboard;
use App\Filament\Wedding\Pages\SetupWedding\Schemas\Form;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingService;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class SetupWedding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.wedding.pages.setup-wedding';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public ?int $weddingId = null;

    /**
     * Determine whether the setup page is available before publication.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->team !== null
            && ! $user->hasPublishedWedding();
    }

    /**
     * Get the setup page title.
     */
    public function getTitle(): string
    {
        return __('wedding.setup.title');
    }

    /**
     * Fill the setup form with the current draft, if one exists.
     */
    public function mount(): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $service = resolve(WeddingService::class);
        $wedding = $service->getWeddingForUserWithoutPublish($user);

        if ($wedding === null) {
            $wedding = $service->saveWeddingData(null, $user, []);
        }

        $this->weddingId = $wedding?->getKey();

        $this->form->model($wedding)->fill($service->getWeddingData($wedding));
    }

    /**
     * Configure the setup wizard form.
     */
    public function form(Schema $schema): Schema
    {
        return Form::configure($schema)->model($this->wedding());
    }

    /**
     * Save the fields completed in the current wizard step as a draft.
     */
    public function saveDraft(array $data): void
    {
        $wedding = $this->saveWedding($data);

        $this->form->model($wedding)->saveRelationships();
    }

    /**
     * Save the completed setup and publish the wedding.
     */
    public function publish(): void
    {
        $this->validate([
            'data.bride_name' => ['required'],
            'data.groom_name' => ['required'],
            'data.wedding_date' => ['required'],
            'data.rsvp_deadline' => ['required'],
            'data.welcome_text' => ['required'],
            'data.Hero' => ['required'],
        ]);

        $wedding = $this->saveWedding($this->form->getState());

        $this->form->model($wedding)->saveRelationships();

        resolve(WeddingService::class)->publishWedding($wedding, $this->getUser());

        Notification::make()
            ->title(__('wedding.notifications.wedding_published'))
            ->success()
            ->send();

        $this->redirect(Dashboard::getUrl());
    }

    /**
     * Get the cancel action back to the dashboard.
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('dashboard')
                ->label(__('wedding.setup.dashboard_action'))
                ->url(Dashboard::getUrl())
                ->color('gray'),
        ];
    }

    /**
     * Save wedding data and associate it with the current user and wedding instance.
     */
    private function saveWedding(array $data): Wedding
    {
        $wedding = resolve(WeddingService::class)->saveWeddingData($this->wedding(), $this->getUser(), $data);
        $this->weddingId = $wedding->getKey();

        return $wedding;
    }

    /**
     * Resolve the current team's wedding for the setup flow.
     */
    private function wedding(): ?Wedding
    {
        $user = $this->getUser();

        if ($this->weddingId === null) {
            return null;
        }

        return Wedding::query()
            ->withoutPublish()
            ->whereKey($this->weddingId)
            ->whereBelongsTo($user->team)
            ->first();
    }

    /**
     * Retrieve the currently authenticated user.
     */
    private function getUser(): User
    {
        return auth()->user();
    }
}
