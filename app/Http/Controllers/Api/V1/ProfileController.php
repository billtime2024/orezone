<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function show(Request $request): UserProfileResource
    {
        $user = $request->user();

        // Auto-create profile if it doesn't exist
        if (! $user->profile) {
            $user->profile()->create(['user_id' => $user->id]);
        }

        $user->load('profile');

        return new UserProfileResource($user->profile);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): UserProfileResource
    {
        $user = $request->user();

        // Update user's name if provided
        if ($request->has('name')) {
            $user->update(['name' => $request->validated('name')]);
        }

        // Get or create the profile
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        // Update profile fields (exclude 'name' as it belongs to users table)
        $profileData = collect($request->validated())->except('name')->filter()->toArray();

        if (! empty($profileData)) {
            $profile->update($profileData);
        }

        return new UserProfileResource($user->profile);
    }

    /**
     * Upload and update the authenticated user's avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $user = $request->user();

        // Delete old avatar if it exists
        if ($user->avatar_path && Storage::disk('avatars')->exists($user->avatar_path)) {
            Storage::disk('avatars')->delete($user->avatar_path);
        }

        // Store the new avatar
        $file = $request->file('avatar');
        $path = $file->store('avatars', 'avatars');

        // Update user's avatar_path
        $user->update(['avatar_path' => $path]);

        return response()->json([
            'avatar_url' => Storage::disk('avatars')->url($path),
        ]);
    }
}
