<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding;

use App\Enums\NavigationGroup;
use App\Filament\Wedding\Pages\ManageWedding\Schemas\Form;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingService;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ManageWedding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.wedding.pages.manage-wedding';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public ?Wedding $record = null;

    public string $activeTab = 'basic';

    /**
     * Determine whether the detailed form is available.
     */
    public static function canAccess(): bool
    {
        return auth()->user()->can('access', Wedding::class);
    }

    /**
     * Get the navigation label.
     */
    public static function getNavigationLabel(): string
    {
        return __('Wedding Details');
    }

    /**
     * Get the navigation group.
     */
    public static function getNavigationGroup(): \UnitEnum
    {
        return NavigationGroup::Wedding;
    }

    /**
     * Get the page title.
     */
    public function getTitle(): string
    {
        return __('Wedding Details');
    }

    /**
     * Mount the page data.
     */
    public function mount(): void
    {
        $service = app(WeddingService::class);
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $this->record = $service->getWeddingForUser($user) ?? Wedding::make();

        $this->form->fill($service->getWeddingData($this->record));
    }

    /**
     * Build the form schema.
     */
    public function form(Schema $schema): Schema
    {
        return Form::make($schema)
            ->model($this->record);
    }

    /**
     * Get available form actions.
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Changes'))
                ->submit('save'),
        ];
    }

    /**
     * Trigger on form saving.
     */
    public function save(): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        try {
            $data = $this->form->getState();
        } catch (ValidationException $exception) {
            $this->activeTab = $this->tabForValidationErrors($exception->errors());
            $this->dispatch('focus-wedding-tab', tab: $this->activeTab);

            throw $exception;
        }

        $this->record = app(WeddingService::class)->saveWeddingData($this->record, $user, $data);

        $this->form->model($this->record)->saveRelationships();

        Notification::make()
            ->title(__('Saved Successfully'))
            ->success()
            ->send();
    }

    /**
     * Find the first tab represented by the validation errors.
     *
     * @param  array<string, array<int, string>>  $errors
     */
    private function tabForValidationErrors(array $errors): string
    {
        foreach (array_keys($errors) as $error) {
            $path = Str::after($error, 'data.');

            return match (true) {
                Str::startsWith($path, ['Hero', 'welcome_text']) => 'appearance',
                Str::startsWith($path, 'timelines') => 'schedule',
                Str::startsWith($path, ['has_memory_wall', 'memory_wall_open_until']) => 'memory',
                Str::startsWith($path, ['meta_title', 'meta_description', 'MetaImage']) => 'meta',
                default => 'basic',
            };
        }

        return 'basic';
    }
}
