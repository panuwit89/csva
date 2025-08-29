<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Tag;
use App\Models\UserDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $request->user()->load('interestedTags', 'documents');

        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Handle interested tags (existing logic)
        $this->syncTags($request, $user);

        // NEW: Handle document uploads
        $this->handleDocumentUploads($request, $user);

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

    private function syncTags(Request $request, $user): void
    {
        if ($request->has('tag_names') && is_array($request->tag_names)) {
            $tagIds = [];
            foreach ($request->tag_names as $tagName) {
                $tagName = trim($tagName);
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
            }
            $user->interestedTags()->sync($tagIds);
        } else {
            $user->interestedTags()->detach();
        }
    }

    private function handleDocumentUploads(Request $request, $user): void
    {
        $request->validate([
            'documents.transcript' => 'nullable|file|mimes:pdf|max:5120', // 5MB Max
            'documents.activity' => 'nullable|file|mimes:pdf|max:5120',
            'documents.receipt' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $type => $file) {
                $existingDocument = $user->documents()->where('document_type', $type)->first();

                if ($existingDocument) {
                    Storage::disk('public')->delete($existingDocument->stored_path);
                }
                $path = $file->store("user_documents/{$user->id}", 'public');

                $user->documents()->updateOrCreate(
                    ['document_type' => $type],
                    [
                        'original_filename' => $file->getClientOriginalName(),
                        'stored_path' => $path,
                        'mime_type' => $file->getMimeType(),
                    ]
                );
            }
        }
    }
    public function destroyDocument(Request $request, UserDocument $document): RedirectResponse|JsonResponse
    {
        if ($document->user_id !== Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        if (Storage::disk('public')->exists($document->stored_path)) {
            Storage::disk('public')->delete($document->stored_path);
        }
        $document->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'File deleted successfully.']);
        }

        return Redirect::route('profile.edit')->with('status', 'document-deleted');
    }

}
