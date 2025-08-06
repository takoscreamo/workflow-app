<?php

namespace App\Domain\Entities;

use App\Domain\Entities\Node;
use Illuminate\Database\Eloquent\Collection;

class Workflow
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly \DateTime $createdAt,
        public readonly \DateTime $updatedAt,
        public readonly Collection $nodes = new Collection()
    ) {}

    public static function create(string $name): self
    {
        return new self(
            id: null,
            name: $name,
            createdAt: new \DateTime(),
            updatedAt: new \DateTime()
        );
    }

    public function withNodes(Collection $nodes): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
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
            createdAt: $this->createdAt,
            updatedAt: new \DateTime(),
            nodes: $this->nodes
        );
    }
}
