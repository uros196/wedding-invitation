<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding\Schemas\Components;

use App\Filament\Schemas\Components\IconSelect;
use App\Models\Group;
use App\Models\WeddingTimeline;
use App\Rules\ChronologicalOrderRule;
use App\Services\GroupService;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TimelineRepeater
{
    /**
     * Generate the repeater for the wedding timeline.
     */
    public static function make(): Repeater
    {
        return Repeater::make('timelines')
            ->relationship('timelines')
            ->label(__('Timeline'))
            ->addActionLabel(__('Add Timeline'))
            ->rules([new ChronologicalOrderRule])
            ->extraItemActions([
                fn (): Action => self::visibilityStatus(),
                fn (): Action => self::visibilityAction(),
            ])
            ->schema([
                Grid::make([
                    'default' => 1,
                    'md' => 4,
                ])
                    ->schema([
                        TimePicker::make('time')
                            ->label(__('Time'))
                            ->placeholder(__('wedding.manage_wedding.schedule.time_placeholder'))
                            ->seconds(false)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 1,
                            ])
                            ->required(),

                        TextInput::make('title')
                            ->label(__('Event Name'))
                            ->placeholder(__('wedding.manage_wedding.schedule.event_name_placeholder'))
                            ->maxLength(100)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->required(),

                        IconSelect::make('icon')
                            ->hintIcon(
                                Heroicon::InformationCircle,
                                __('wedding.manage_wedding.schedule.icon_help'),
                            )
                            ->columnSpan([
                                'default' => 1,
                                'md' => 1,
                            ]),
                    ]),

                Grid::make([
                    'default' => 1,
                    'md' => 2,
                ])
                    ->schema([
                        TextInput::make('address')
                            ->label(__('Address'))
                            ->placeholder(__('wedding.manage_wedding.schedule.address_placeholder'))
                            ->maxLength(100),

                        TextInput::make('map_url')
                            ->label(__('Map Link'))
                            ->placeholder(__('wedding.manage_wedding.schedule.map_link_placeholder'))
                            ->url()
                            ->maxLength(255)
                            ->suffixAction(
                                Action::make('open_map')
                                    ->icon(Heroicon::ArrowTopRightOnSquare)
                                    ->url(fn (?string $state): ?string => $state)
                                    ->openUrlInNewTab()
                                    ->visible(fn (?string $state): bool => filled($state)),
                            ),
                    ]),
                Toggle::make('is_visible')
                    ->label(__('wedding.manage_wedding.schedule.visibility_toggle'))
                    ->hintIcon(
                        Heroicon::InformationCircle,
                        __('wedding.manage_wedding.schedule.visibility_help'),
                    )
                    ->default(true),
            ])
            ->columns(1)
            ->collapsed()
            ->reorderable()
            ->orderColumn('sort_order')
            ->defaultItems(0)
            ->live()
            ->itemLabel(fn (Schema $item): ?string => self::timelineItemLabel($item));
    }

    /**
     * Build a compact item label from the persisted timeline record.
     *
     * The repeater state is intentionally not used here, so labels remain stable
     * while the user edits an item and updates only after a successful save.
     */
    private static function timelineItemLabel(Schema $item): ?string
    {
        $timeline = $item->getRecord();

        if (! $timeline instanceof WeddingTimeline || ! $timeline->exists) {
            return null;
        }

        return $timeline->repeater_label;
    }

    /**
     * Build the action used to manage the groups that can see a timeline item.
     */
    private static function visibilityAction(): Action
    {
        $groupService = resolve(GroupService::class);

        return Action::make('manage_visibility')
            ->label(fn (Action $action): string => self::visibilityLabel(self::timelineForAction($action)))
            ->icon(fn (Action $action): Heroicon => self::visibilityIcon(self::timelineForAction($action)))
            ->color(fn (Action $action): string => self::visibilityColor(self::timelineForAction($action)))
            ->tooltip(fn (Action $action): string => self::visibilityTooltip(self::timelineForAction($action)))
            ->modalHeading(function (Action $action): string {
                $timeline = self::timelineForAction($action);

                return __('wedding.manage_wedding.schedule.visibility_modal_heading', [
                    'event' => $timeline ? $timeline->title : __('Timeline'),
                ]);
            })
            ->modalDescription(__('wedding.manage_wedding.schedule.visibility_modal_description'))
            ->modalSubmitActionLabel(__('wedding.manage_wedding.schedule.visibility_save'))
            ->modalWidth('2xl')
            ->disabled(fn (Action $action): bool => self::timelineForAction($action) === null)
            ->schema(fn (Action $action): array => [
                CheckboxList::make('visible_group_ids')
                    ->label(__('wedding.manage_wedding.schedule.visible_groups'))
                    ->options(self::groupOptions(self::timelineForAction($action)))
                    ->default(fn (): array => self::visibleGroupIds(self::timelineForAction($action)))
                    ->selectAllAction(
                        fn (Action $action): Action => $action->label(
                            __('wedding.manage_wedding.schedule.visibility_select_all'),
                        ),
                    )
                    ->deselectAllAction(
                        fn (Action $action): Action => $action->label(
                            __('wedding.manage_wedding.schedule.visibility_deselect_all'),
                        ),
                    )
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(2),
            ])
            ->action(function (Action $action, array $data) use ($groupService): void {
                $groupService->saveVisibility(self::timelineForAction($action), $data['visible_group_ids'] ?? []);
            });
    }

    /**
     * Create an action for managing the visibility status of a wedding schedule.
     */
    private static function visibilityStatus(): Action
    {
        return Action::make('visibility_status')
            ->label(__('wedding.manage_wedding.schedule.visibility_inactive_label'))
            ->badge()
            ->color('warning')
            ->hiddenLabel()
            ->visible(function (Action $action): bool {
                $record = self::timelineForAction($action);

                return $record?->exists === true && ! $record->is_visible;
            });
    }

    /**
     * Resolve the timeline record associated with the repeater action.
     *
     * A timeline is available only after the repeater item has been persisted.
     */
    private static function timelineForAction(Action $action): ?WeddingTimeline
    {
        $repeater = $action->getSchemaComponent();
        $itemKey = data_get($action->getArguments(), 'item');

        if (! $repeater instanceof Repeater || blank($itemKey)) {
            return null;
        }

        $record = data_get($repeater->getItems(), $itemKey)?->getRecord();

        return $record instanceof WeddingTimeline && $record->exists ? $record : null;
    }

    /**
     * Get all groups belonging to the timeline's wedding for the visibility form.
     */
    private static function groupOptions(?WeddingTimeline $timeline): array
    {
        if (! $timeline) {
            return [];
        }

        return Group::query()
            ->where('wedding_id', $timeline->wedding_id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * Get the IDs of all groups belonging to the timeline's wedding.
     */
    private static function weddingGroupIds(WeddingTimeline $timeline): array
    {
        return Group::query()
            ->where('wedding_id', $timeline->wedding_id)
            ->pluck('id')
            ->map(static fn (mixed $groupId): int => (int) $groupId)
            ->all();
    }

    /**
     * Get the IDs of groups for which the timeline item is hidden.
     */
    private static function hiddenGroupIds(WeddingTimeline $timeline): array
    {
        return array_map(
            static fn (mixed $groupId): int => (int) $groupId,
            $timeline->hiddenByGroups()->pluck('groups.id')->all(),
        );
    }

    /**
     * Get the IDs of groups that can currently see the timeline item.
     *
     * Visibility is calculated as all wedding groups minus the groups marked
     * as hidden for the timeline item.
     */
    private static function visibleGroupIds(?WeddingTimeline $timeline): array
    {
        if (! $timeline) {
            return [];
        }

        return array_values(array_diff(
            self::weddingGroupIds($timeline),
            self::hiddenGroupIds($timeline),
        ));
    }

    /**
     * Determine whether the timeline item has custom group visibility.
     */
    private static function hasCustomVisibility(?WeddingTimeline $timeline): bool
    {
        return $timeline !== null && self::hiddenGroupIds($timeline) !== [];
    }

    /**
     * Get the action label based on the timeline item's visibility settings.
     */
    private static function visibilityLabel(?WeddingTimeline $timeline): string
    {
        return self::hasCustomVisibility($timeline)
            ? __('wedding.manage_wedding.schedule.visibility_custom')
            : __('wedding.manage_wedding.schedule.visibility_all');
    }

    /**
     * Get the action tooltip based on whether the timeline item can be managed.
     */
    private static function visibilityTooltip(?WeddingTimeline $timeline): string
    {
        return $timeline
            ? __('wedding.manage_wedding.schedule.visibility_manage')
            : __('wedding.manage_wedding.schedule.visibility_save_first');
    }

    /**
     * Get the action color based on the timeline item's visibility settings.
     */
    private static function visibilityColor(?WeddingTimeline $timeline): string
    {
        return self::hasCustomVisibility($timeline) ? 'warning' : 'success';
    }

    /**
     * Get the action icon based on the timeline item's visibility settings.
     */
    private static function visibilityIcon(?WeddingTimeline $timeline): Heroicon
    {
        return self::hasCustomVisibility($timeline) ? Heroicon::UserGroup : Heroicon::Eye;
    }
}
