<?php

declare(strict_types=1);

namespace App\Enums;

enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
}
