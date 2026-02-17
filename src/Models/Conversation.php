<?php

namespace PapaRascalDev\Sidekick\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasUuids;

    protected $table = 'sidekick_conversations';

    protected $fillable = [
        'provider',
        'model',
        'system_prompt',
        'max_tokens',
    ];

    protected $casts = [
        'max_tokens' => 'integer',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class, 'conversation_id');
    }
}
