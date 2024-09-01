<?php

namespace PapaRascalDev\Sidekick\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    use HasUuids;
    protected $guarded = ['id'];
    protected $table   = 'sidekick_conversation_messages';
}
