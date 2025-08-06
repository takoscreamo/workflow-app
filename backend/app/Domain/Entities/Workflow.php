<?php

namespace App\Domain\Entities;

use App\Domain\Entities\Node;
use Illuminate\Database\Eloquent\Collection;

class Workflow
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $inputType,
        public readonly string $outputType,
        public readonly ?string $inputData,
        public readonly \DateTime $createdAt,
        public readonly \DateTime $updatedAt,
        public readonly Collection $nodes = new Collection()
    ) {}

    public static function create(string $name, string $inputType = 'text', string $outputType = 'text', ?string $inputData = null): self
    {
        return new self(
            id: null,
            name: $name,
            inputType: $inputType,
            outputType: $outputType,
            inputData: $inputData,
            createdAt: new \DateTime(),
            updatedAt: new \DateTime()
        );
    }

    public function withNodes(Collection $nodes): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            inputType: $this->inputType,
            outputType: $this->outputType,
            inputData: $this->inputData,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
            nodes: $nodes
        );
    }

    public function updateName(string $name): self
    {
        return new self(
            id: $this->id,
            name: $name,
            inputType: $this->inputType,
            outputType: $this->outputType,
            inputData: $this->inputData,
            createdAt: $this->createdAt,
            updatedAt: new \DateTime(),
            nodes: $this->nodes
        );
    }

    public function updateInputOutputConfig(string $inputType, string $outputType, ?string $inputData = null): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            inputType: $inputType,
            outputType: $outputType,
            inputData: $inputData,
            createdAt: $this->createdAt,
            updatedAt: new \DateTime(),
            nodes: $this->nodes
        );
    }
}
