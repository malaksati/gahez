<?php

namespace App\Policies;

use App\Models\Support;
use App\Models\User;

class SupportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage support-chats');
    }

    public function view(User $user, Support $support): bool
    {
        if ($support->user_id === $user->id) {
            return true;
        }

        return $user->can('manage support-chats');
    }

    public function create(User $user): bool
    {
        return $user->role === 'user' || $user->hasRole('user');
    }

    public function update(User $user, Support $support): bool
    {
        return $user->can('manage support-chats');
    }

    public function sendMessage(User $user, Support $support): bool
    {
        if ($support->isClosed()) {
            return false;
        }

        return $this->view($user, $support);
    }
}
