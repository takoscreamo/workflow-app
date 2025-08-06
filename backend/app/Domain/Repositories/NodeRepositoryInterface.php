<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Node;

interface NodeRepositoryInterface
{
    public function findByWorkflowId(int $workflowId): array;
    public function save(Node $node): Node;
    public function deleteByWorkflowId(int $workflowId): void;
    public function deleteById(int $nodeId): bool;
}
