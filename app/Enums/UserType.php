<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Defines the types of users that can access the Filament panels.
 */
enum UserType: string
{
    case ManagementAdmin = 'management_admin';
    case WeddingUser = 'wedding_user';
}
