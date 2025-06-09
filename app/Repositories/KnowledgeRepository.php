<?php

namespace App\Repositories;

use App\Models\Knowledge;
use App\Repositories\Traits\SimpleCRUD;

class KnowledgeRepository
{
    use SimpleCRUD;

    private string $model = Knowledge::class;

    public function getAllKnowledgeDesc()
    {
        return Knowledge::with('uploader')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getActivatedKnowledgeDesc()
    {
        return Knowledge::where('is_active', true)
            ->select([
                'id',
                'title',
                'filename',
                'original_filename',
                'file_path',
                'file_size',
                'mime_type',
                'description',
                'created_at',
                'updated_at'
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getActivatedKnowledgeById(int $id)
    {
        return Knowledge::where('is_active', true)->findOrFail($id);
    }

    public function getStats()
    {
        $stats = [
            'total_files' => Knowledge::count(),
            'active_files' => Knowledge::where('is_active', true)->count(),
            'inactive_files' => Knowledge::where('is_active', false)->count(),
            'total_size' => Knowledge::where('is_active', true)->sum('file_size'),
            'last_updated' => Knowledge::latest('updated_at')->first()?->updated_at
        ];

        return $stats;
    }

    public function getActiveCount()
    {
        return Knowledge::where('is_active', true)->count();
    }
}
