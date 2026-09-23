<?php

declare(strict_types=1);

namespace App\Filament\Wedding\Pages\ManageWedding;

use App\Filament\Wedding\Resources\MemoryWallShares\Actions\CreateMemoryWallShareAction;
use App\Filament\Wedding\Resources\MemoryWallShares\Tables\MemoryWallSharesTable;
use App\Models\MemoryWallShare;
use App\Models\User;
use App\Models\Wedding;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MemoryWallSharesManager extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function render(): View
    {
        return view('filament.wedding.pages.manage-wedding.memory-wall-shares-table');
    }

    public function table(Table $table): Table
    {
        return MemoryWallSharesTable::configure($table)
            ->query($this->wedding()->memoryWallShares()->getQuery())
            ->headerActions([
                CreateMemoryWallShareAction::make(),
            ]);
    }

    private function wedding(): Wedding
    {
        $user = auth()->user();

        abort_unless(
            $user instanceof User && $user->can('viewAny', MemoryWallShare::class),
            403,
        );

        $wedding = $user->team?->wedding;

        abort_unless($wedding instanceof Wedding, 403);

        return $wedding;
    }
}
