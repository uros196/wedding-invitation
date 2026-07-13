<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;

class GroupController
{
    public function show(Group $group)
    {
        $group->load('guests');
    }
}
