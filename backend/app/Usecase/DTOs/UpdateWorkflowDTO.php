<?php

namespace App\Usecase\DTOs;

class UpdateWorkflowDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {}

    public static function fromRequest(int $id, array $data): self
    {
        return new self(
            id: $id,
            name: $data['name']
        );
    }
}
