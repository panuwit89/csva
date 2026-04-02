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

    public function getActiveKnowledge()
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
}
