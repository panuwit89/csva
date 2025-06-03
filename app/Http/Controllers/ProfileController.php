<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // DEBUG: Let's see what data is being sent
        Log::info('Form data received:', [
            'tag_names' => $request->tag_names,
            'all_request' => $request->all()
        ]);

        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        // Handle interested tags
        if ($request->has('tag_names') && is_array($request->tag_names)) {
            $tagIds = [];

            foreach ($request->tag_names as $tagName) {
                $tagName = trim($tagName);
                if (!empty($tagName)) {
                    // Find or create the tag
                    $tag = Tag::firstOrCreate(
                        ['name' => $tagName],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $tag->id;
                }
            }

            // Sync the tags (this will remove old ones and add new ones)
            $request->user()->interestedTags()->sync($tagIds);

            Log::info('Tags synced:', [
                'tag_ids' => $tagIds,
                'tag_names' => $request->tag_names
            ]);
        } else {
            // Remove all tags if none selected
            $request->user()->interestedTags()->detach();
            Log::info('All tags detached - no tag_names in request');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
