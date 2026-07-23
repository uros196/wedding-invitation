<?php

declare(strict_types=1);

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login;
use Illuminate\Contracts\Support\Htmlable;

class ManagementLogin extends Login
{
    /**
     * Get login form heading.
     */
    public function getHeading(): string|Htmlable|null
    {
        return __('auth.login.management_heading');
    }
}
