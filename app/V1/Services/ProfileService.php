<?php

namespace App\V1\Services;

use App\Models\User;
use App\V1\Support\UploadStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function update(User $user, array $data, ?UploadedFile $image = null, bool $removeImage = false): User
    {
        if ($removeImage) {
            $this->deleteStoredImage($user);
            $data['image'] = null;
        } elseif ($image instanceof UploadedFile && $image->isValid()) {
            $this->deleteStoredImage($user);
            $data['image'] = UploadStorage::store($image, 'avatars', 'public');
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['password_confirmation'], $data['remove_image']);

        $user->update($data);

        return $user->fresh(['roles', 'permissions']);
    }

    protected function deleteStoredImage(User $user): void
    {
        $path = $user->getRawOriginal('image');

        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
