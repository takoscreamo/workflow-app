<?php

namespace App\Usecase;

use App\Usecase\DTOs\CreateWorkflowDTO;
use App\Usecase\DTOs\UpdateWorkflowDTO;
use App\Usecase\DTOs\AddNodeDTO;
use App\Domain\Entities\Workflow;
use App\Domain\Entities\Node;
use App\Domain\Entities\NodeType;
use App\Domain\Repositories\WorkflowRepositoryInterface;
use App\Domain\Repositories\NodeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WorkflowUsecase
{
    public function __construct(
        private WorkflowRepositoryInterface $workflowRepository,
        private NodeRepositoryInterface $nodeRepository
    ) {}

    public function getAllWorkflows(): Collection
    {
        return $this->workflowRepository->findAll();
    }

    public function getWorkflowById(int $id): ?Workflow
    {
        return $this->workflowRepository->findById($id);
    }

    public function createWorkflow(CreateWorkflowDTO $dto): Workflow
    {
        $workflow = Workflow::create($dto->name);
        return $this->workflowRepository->save($workflow);
    }

    public function updateWorkflow(UpdateWorkflowDTO $dto): ?Workflow
    {
        $workflow = $this->workflowRepository->findById($dto->id);
        if (!$workflow) {
            return null;
        }

        $updatedWorkflow = $workflow->updateName($dto->name);
        return $this->workflowRepository->save($updatedWorkflow);
    }

    public function deleteWorkflow(int $id): bool
    {
        $workflow = $this->workflowRepository->findById($id);
        if (!$workflow) {
            return false;
        }

        $this->workflowRepository->delete($id);
        return true;
    }

    public function addNode(AddNodeDTO $dto): Node
    {
        $node = Node::create($dto->workflowId, $dto->nodeType, $dto->config);
        return $this->nodeRepository->save($node);
    }

    public function runWorkflow(int $id): array
    {
        $workflow = $this->workflowRepository->findById($id);
        if (!$workflow) {
            throw new \Exception('ワークフローが見つかりません');
        }

        // TODO: 実際のワークフロー実行ロジックを実装
        return [
            'message' => 'ワークフローが実行されました',
            'result' => []
        ];
    }
}
