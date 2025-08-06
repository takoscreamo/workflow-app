<?php

namespace App\Usecase\DTOs;

class CreateWorkflowDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $inputType,
        public readonly string $outputType,
        public readonly ?string $inputData
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            inputType: $data['input_type'] ?? 'text',
            outputType: $data['output_type'] ?? 'text',
            inputData: $data['input_data'] ?? null
        );
    }
}
