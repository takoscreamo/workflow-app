<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Workflow;

interface WorkflowRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Workflow;
    public function save(Workflow $workflow): Workflow;
    public function delete(int $id): void;
}
