<?php

namespace PapaRascalDev\Sidekick\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeBase extends Model
{
    use HasUuids;

    protected $table = 'sidekick_knowledge_bases';

    protected $fillable = [
        'name',
        'description',
        'embedding_provider',
        'embedding_model',
        'chunk_size',
        'chunk_overlap',
    ];

    protected $casts = [
        'chunk_size' => 'integer',
        'chunk_overlap' => 'integer',
    ];

    public function chunks(): HasMany
    {
        return $this->hasMany(KnowledgeChunk::class, 'knowledge_base_id');
    }
}
