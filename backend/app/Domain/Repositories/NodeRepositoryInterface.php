<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Node;
use Illuminate\Database\Eloquent\Collection;

interface NodeRepositoryInterface
{
    public function findByWorkflowId(int $workflowId): Collection;
    public function save(Node $node): Node;
    public function deleteByWorkflowId(int $workflowId): void;
}
