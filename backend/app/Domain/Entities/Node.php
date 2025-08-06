<?php

namespace App\Domain\Entities;

use App\Domain\Entities\NodeType;

class Node
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $workflowId,
        public readonly NodeType $nodeType,
        public readonly array $config,
        public readonly \DateTime $createdAt,
        public readonly \DateTime $updatedAt
    ) {}

    public static function create(int $workflowId, NodeType $nodeType, array $config): self
    {
        return new self(
            id: null,
            workflowId: $workflowId,
            nodeType: $nodeType,
            config: $config,
            createdAt: new \DateTime(),
            updatedAt: new \DateTime()
        );
    }
}
