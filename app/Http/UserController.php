<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::with(['videos' => function ($query) {
            $query->where('is_public', true)->orderBy('created_at', 'desc')->limit(12);
        }])->findOrFail($id);

        return response()->json($user);
    }

    public function follow(Request $request, $id)
    {
        $userToFollow = User::findOrFail($id);
        $currentUser = $request->user();

        if ($currentUser->id === $userToFollow->id) {
            return response()->json(['message' => 'You cannot follow yourself'], 400);
        }

        if ($currentUser->isFollowing($userToFollow->id)) {
            $currentUser->following()->detach($userToFollow->id);
            $currentUser->decrement('following_count');
            $userToFollow->decrement('followers_count');
            $following = false;
        } else {
            $currentUser->following()->attach($userToFollow->id);
            $currentUser->increment('following_count');
            $userToFollow->increment('followers_count');
            $following = true;
        }

        return response()->json([
            'following' => $following,
            'followers_count' => $userToFollow->followers_count
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|string|max:255|unique:users,username,' . $request->user()->id,
            'bio' => 'nullable|string|max:500',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $updateData = $request->only(['username', 'bio']);

        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->profile_image));
            }

            $imagePath = $request->file('profile_image')->store('profiles', 'public');
            $updateData['profile_image'] = Storage::url($imagePath);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}