<?php

namespace App\V1\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class NotificationService
{
    public function notifyUser(?User $user, Notification $notification): void
    {
        if (! $user) {
            return;
        }

        $user->notify($notification);
    }

    /**
     * @return Collection<int, User>
     */
    public function adminUsers(): Collection
    {
        $users = new Collection;

        foreach (['admin', 'super-admin'] as $role) {
            try {
                $users = $users->merge(User::role($role)->get());
            } catch (RoleDoesNotExist) {
                continue;
            }
        }

        return $users->unique('id')->values();
    }

    public function notifyAdmins(Notification $notification): void
    {
        $this->adminUsers()->each(fn (User $admin) => $admin->notify($notification));
    }
}
