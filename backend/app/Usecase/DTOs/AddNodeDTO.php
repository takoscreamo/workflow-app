<?php

namespace App\Usecase\DTOs;

use App\Domain\Entities\NodeType;

class AddNodeDTO
{
    public function __construct(
        public readonly int $workflowId,
        public readonly NodeType $nodeType,
        public readonly array $config
    ) {}

    public static function fromRequest(int $workflowId, array $data): self
    {
        return new self(
            workflowId: $workflowId,
            nodeType: NodeType::from($data['node_type']),
            config: $data['config']
        );
    }
}
