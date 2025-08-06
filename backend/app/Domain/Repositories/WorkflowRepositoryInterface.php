<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Workflow;
use Illuminate\Database\Eloquent\Collection;

interface WorkflowRepositoryInterface
{
    public function findAll(): Collection;
    public function findById(int $id): ?Workflow;
    public function save(Workflow $workflow): Workflow;
    public function delete(int $id): void;
}
