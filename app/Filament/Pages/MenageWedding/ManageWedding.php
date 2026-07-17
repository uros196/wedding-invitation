<?php

declare(strict_types=1);

namespace App\Filament\Pages\MenageWedding;

use App\Filament\Pages\MenageWedding\Schemas\Form;
use App\Models\Wedding;
use App\Services\WeddingService;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageWedding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.manage-wedding';

    protected static ?string $title = 'Detalji venčanja';

    public ?array $data = [];

    public ?Wedding $record = null;

    /**
     * Get the navigation label.
     */
    public static function getNavigationLabel(): string
    {
        return __('Wedding Details');
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

        $this->record = $service->getWedding();

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
        $data = $this->form->getState();

        app(WeddingService::class)->saveWeddingData($this->record, $data);

        $this->form->model($this->record)->saveRelationships();

        Notification::make()
            ->title(__('Saved Successfully'))
            ->success()
            ->send();
    }
}
