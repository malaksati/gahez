<?php

use App\Models\Support;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('support.{support}', function (User $user, Support $support) {
    if ($support->user_id === $user->id) {
        return true;
    }

    return $user->can('manage support-chats');
}, ['guards' => ['sanctum', 'web']]);
