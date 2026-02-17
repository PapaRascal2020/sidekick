<?php

namespace PapaRascalDev\Sidekick\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeChunk extends Model
{
    use HasUuids;

    protected $table = 'sidekick_knowledge_chunks';

    protected $fillable = [
        'knowledge_base_id',
        'content',
        'embedding',
        'source',
        'chunk_index',
        'metadata',
    ];

    protected $casts = [
        'embedding' => 'array',
        'metadata' => 'array',
        'chunk_index' => 'integer',
    ];

    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class, 'knowledge_base_id');
    }
}
