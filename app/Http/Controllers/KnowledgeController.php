<?php

namespace App\Http\Controllers;

use App\Models\Knowledge;
use App\Repositories\KnowledgeRepository;
use App\Services\FastAPIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class KnowledgeController extends Controller
{
    public function __construct(
        private KnowledgeRepository $knowledgeRepository,
        private FastAPIService $fastAPIService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $knowledges = $this->knowledgeRepository->getAllKnowledgeDesc();

        return view('knowledge.index', compact('knowledges'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('knowledge.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file' => [
                'required',
                File::types(['pdf'])
                    ->max(10 * 1024) // 10MB
            ]
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $filename = Str::uuid() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.pdf';

            // Store in doc directory to match your Python setup
            $filePath = $file->storeAs('doc', $filename, 'public');

            $this->knowledgeRepository->create([
                'title' => $request->title,
                'filename' => $filename,
                'original_filename' => $originalName,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'description' => $request->description,
                'uploaded_by' => Auth::id(),
            ]);

            return redirect()->route('knowledge.index')
                ->with('success', 'Knowledge document uploaded successfully!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Knowledge $knowledge)
    {
        return view('knowledge.show', compact('knowledge'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Knowledge $knowledge)
    {
        return view('knowledge.edit', compact('knowledge'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Knowledge $knowledge)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file' => [
                'nullable',
                File::types(['pdf'])
                    ->max(10 * 1024) // 10MB
            ]
        ]);

        try {
            $data = [
                'title' => $request->title,
                'description' => $request->description,
            ];

            // If new file is uploaded
            if ($request->hasFile('file')) {
                // Delete old file
                if (Storage::disk('public')->exists($knowledge->file_path)) {
                    Storage::disk('public')->delete($knowledge->file_path);
                }

                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $filename = Str::uuid() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.pdf';

                $filePath = $file->storeAs('doc', $filename, 'public');

                $data = array_merge($data, [
                    'filename' => $filename,
                    'original_filename' => $originalName,
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }

            $this->knowledgeRepository->update($data, $knowledge->id);

            return redirect()->route('knowledge.index')
                ->with('success', 'Knowledge document updated successfully!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update document: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Knowledge $knowledge)
    {
        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($knowledge->file_path)) {
                Storage::disk('public')->delete($knowledge->file_path);
            }

            $knowledge->delete();

            return redirect()->route('knowledge.index')
                ->with('success', 'Knowledge document deleted successfully!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete document: ' . $e->getMessage());
        }
    }

    public function download(Knowledge $knowledge)
    {
        if (!Storage::disk('public')->exists($knowledge->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download(
            $knowledge->file_path,
            $knowledge->original_filename
        );
    }

    public function toggle(Knowledge $knowledge)
    {
        $knowledge->update(['is_active' => !$knowledge->is_active]);

        $status = $knowledge->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Knowledge document {$status} successfully!");
    }

    public function refreshKnowledge()
    {
        try {
            $result = $this->fastAPIService->refreshKnowledge();

            if ($result['success']) {
                return back()->with('success', $result['message']);
            } else {
                return back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to refresh AI knowledge base: ' . $e->getMessage());
        }
    }

    public function getActiveKnowledge(): JsonResponse
    {
        try {
            $knowledges = $this->knowledgeRepository->getActiveKnowledge();

            return response()->json($knowledges);

        } catch (\Exception $e) {
            Log::error('Error fetching active knowledge files: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to fetch knowledge files',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getKnowledgeStats(): JsonResponse
    {
        try {
            $stats = $this->knowledgeRepository->getStats();

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Error fetching knowledge statistics: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to fetch statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
