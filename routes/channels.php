<?php

use App\Enums\FilamentPanel;
use App\Enums\TeamType;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Authorize user's private channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => FilamentPanel::guards()]);

// Authorize wedding team private channel
Broadcast::channel(TeamType::Wedding->broadcastChannelName(), function (User $user, Team $team): bool {
    return $user->team->is($team) && $user->team->type->isWedding();
}, ['guards' => FilamentPanel::guards()]);
