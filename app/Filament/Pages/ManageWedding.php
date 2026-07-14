<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Wedding;
use App\Services\WeddingService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
        return $schema
            ->model($this->record)
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make(__('messages.basic_info'))
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('bride_name')
                                            ->label(__('Bride\'s Name'))
                                            ->required(),
                                        TextInput::make('groom_name')
                                            ->label(__('Groom\'s Name'))
                                            ->required(),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        DatePicker::make('wedding_date')
                                            ->label(__('Wedding Date and Time'))
                                            ->required(),
                                        DateTimePicker::make('rsvp_deadline')
                                            ->label(__('RSVP Deadline'))
                                            ->required(),
                                    ]),
                            ]),

                        Section::make(__('Main Image'))
                            ->description(__('This image is displayed at the top of the invitation.'))
                            ->columnSpan(1)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('hero')
                                    ->hiddenLabel()
                                    ->collection('hero')
                                    ->image()
                                    ->imageEditor()
                                    ->columnSpanFull(),
                            ]),
                    ]),



                Section::make(__('Invitation Text'))
                    ->schema([
                        RichEditor::make('welcome_text')
                            ->label(__('Main Text'))
                            ->extraInputAttributes(['style' => 'min-height: 300px;'])
                            ->required(),
                    ]),

                Section::make(__('Schedule'))
                    ->schema([
                        Repeater::make('schedules')
                            ->label(__('Events'))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('Event Name'))
                                            ->required(),
                                        TimePicker::make('time')
                                            ->label(__('Time')),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('location')
                                            ->label(__('Location')),
                                        Toggle::make('enabled')
                                            ->label(__('Visible'))
                                            ->default(true),
                                    ]),
                            ])
                            ->columns(1)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                    ]),

                Section::make(__('Meta Data'))
                    ->description(__('Used to generate the link preview when the invitation is shared.'))
                    ->columns(3)
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label(__('Meta Title'))
                                    ->placeholder(__(config('wedding.meta.title')))
                                    ->maxLength(255),

                                Textarea::make('meta_description')
                                    ->label(__('Meta Description'))
                                    ->placeholder(__(config('wedding.meta.description')))
                                    ->rows(3)
                            ]),

                        Grid::make(1)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('meta_image')
                                    ->label(__('Meta Image'))
                                    ->helperText(__('Optional. If left empty, the main image above will be used.'))
                                    ->collection('meta_image')
                                    ->image()
                                    ->imageEditor(),
                            ]),
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
