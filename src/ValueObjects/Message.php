<?php

namespace PapaRascalDev\Sidekick\ValueObjects;

use PapaRascalDev\Sidekick\Enums\Role;

readonly class Message
{
    public function __construct(
        public Role $role,
        public string $content,
    ) {}

    public function toArray(): array
    {
        return [
            'role' => $this->role->value,
            'content' => $this->content,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            role: Role::from($data['role']),
            content: $data['content'],
        );
    }
}
