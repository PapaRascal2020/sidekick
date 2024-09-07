<?php

namespace PapaRascalDev\Sidekick\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasUuids;

    protected $guarded = ['id'];
    protected $table = 'sidekick_conversations';

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class);
    }

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
