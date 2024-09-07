<?php

namespace PapaRascalDev\Sidekick\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    use HasUuids;
    protected $guarded = ['id'];

    protected $table   = 'sidekick_conversation_messages';


    protected $visible = ['role', 'content'];

    public function toCustomArray(
        array $mappings = [],
    ): array
    {
        $array = parent::toArray();

        foreach ($mappings as $newKey => $oldKey) {
            $array[$newKey] = $array[$oldKey];
            unset($array[$oldKey]);
        }
        return $array;
    }
}
