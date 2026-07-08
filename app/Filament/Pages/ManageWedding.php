<?php

namespace App\Filament\Pages;

use App\Services\WeddingService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManageWedding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.manage-wedding';

    protected static ?string $title = 'Detalji venčanja';

    protected static ?string $navigationLabel = 'Detalji venčanja';

    public ?array $data = [];

    /**
     * Mount the page data.
     */
    public function mount(): void
    {
        $this->form->fill(app(WeddingService::class)->getWeddingData());
    }

    /**
     * Build the form schema.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Osnovne informacije')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('bride_name')
                                    ->label('Ime mlade')
                                    ->required(),
                                TextInput::make('groom_name')
                                    ->label('Ime mladoženje')
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('wedding_date')
                                    ->label('Datum i vreme venčanja')
                                    ->required(),
                                DateTimePicker::make('rsvp_deadline')
                                    ->label('Rok za prijavu gostiju')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Tekst pozivnice')
                    ->schema([
                        Textarea::make('welcome_text')
                            ->label('Glavni tekst')
                            ->rows(5)
                            ->required(),
                    ]),

                Section::make('Satnica')
                    ->schema([
                        Repeater::make('schedules')
                            ->label('Događaji')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Naziv događaja')
                                            ->required(),
                                        TimePicker::make('time')
                                            ->label('Vreme'),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('location')
                                            ->label('Lokacija'),
                                        Toggle::make('enabled')
                                            ->label('Vidljivo')
                                            ->default(true),
                                    ]),
                            ])
                            ->columns(1)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Get available form actions.
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Sačuvaj izmene')
                ->submit('save'),
        ];
    }

    /**
     * Trigger on form saving.
     */
    public function save(): void
    {
        $data = $this->form->getState();

        app(WeddingService::class)->saveWeddingData($data);

        Notification::make()
            ->title('Uspešno sačuvano')
            ->success()
            ->send();
    }
}
