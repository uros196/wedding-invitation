<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\GroupResource;
use App\Http\Resources\MetaDataResource;
use App\Http\Resources\WeddingResource;
use App\Models\Group;
use App\Services\GroupService;
use App\Services\WeddingService;
use App\Support\MetaFactory;
use Inertia\Inertia;
use Inertia\Response;

class GroupController
{
    public function __construct(
        protected GroupService $groupService,
        protected WeddingService $weddingService,
        protected MetaFactory $metaFactory,
    ) {}

    /**
     * Render the invitation page.
     */
    public function show(Group $group): Response
    {
        $group->load('guests', 'wedding');

        // Get available timelines for the group and attach them to the wedding
        $group->wedding->setRelation('timelines', $this->groupService->getAvailableTimeline($group));

        $metaData = $this->metaFactory->forGroup($group);

        return Inertia::render('invitation', [
            'metaData' => MetaDataResource::make($metaData),
            'wedding' => WeddingResource::make($group->wedding),
            'group' => GroupResource::make($group),
        ]);
    }
}
