<?php

namespace PapaRascalDev\Sidekick\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SidekickConversation extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    public function messages(): HasMany
    {
        return $this->hasMany(SidekickConversationMessage::class, 'conversation_id');
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
