<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\FilamentPanel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:seed-development-data')]
#[Description('Seed development data for selected Filament panels.')]
class SeedDevelopmentData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info(__('messages.dev_seed.starting'));
        $this->line(__('messages.dev_seed.management_user_intro'));

        $managementUserExitCode = $this->call(CreateManagementUser::class);

        if ($managementUserExitCode !== self::SUCCESS) {
            return $managementUserExitCode;
        }

        $seeders = FilamentPanel::devSeedFor();

        if ($seeders === []) {
            $this->error(__('messages.dev_seed.no_panels'));

            return self::FAILURE;
        }

        $this->line(__('messages.dev_seed.panel_selection_intro'));

        /** @var array<int, string> $selectedPanels */
        $selectedPanels = $this->choice(
            __('messages.dev_seed.select_panels'),
            array_keys($seeders),
            null,
            null,
            true,
        );

        foreach ($selectedPanels as $panel) {
            $seeder = $seeders[$panel] ?? null;

            if ($seeder === null) {
                $this->error(__('messages.dev_seed.invalid_panel', ['panel' => $panel]));

                return self::FAILURE;
            }

            $this->info(__('messages.dev_seed.panel_started', ['panel' => $panel]));

            $seederExitCode = $this->call('db:seed', [
                '--class' => $seeder,
            ]);

            if ($seederExitCode !== self::SUCCESS) {
                return $seederExitCode;
            }

            $this->info(__('messages.dev_seed.panel_completed', ['panel' => $panel]));
        }

        $this->info(__('messages.dev_seed.completed'));
        $this->line(__('messages.dev_seed.management_credentials', [
            'path' => FilamentPanel::Management->path(),
        ]));

        return self::SUCCESS;
    }
}
