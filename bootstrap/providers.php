<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\ManagementPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    ManagementPanelProvider::class,
];
