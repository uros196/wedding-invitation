<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\Filament\WeddingPanelProvider;
use App\Providers\Filament\ManagementPanelProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    WeddingPanelProvider::class,
    ManagementPanelProvider::class,
];
