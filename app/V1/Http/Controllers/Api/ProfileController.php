<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\UpdateProfileRequest;
use App\V1\Http\Resources\Api\UserResource;
use App\V1\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profiles,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles', 'permissions');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profiles->update(
            $request->user(),
            $request->safe()->except(['image', 'remove_image']),
            $request->file('image'),
            $request->boolean('remove_image'),
        );

        $user->load('roles', 'permissions');

        return response()->json([
            'success' => true,
            'message' => __('messages.Profile updated successfully.'),
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }
}
