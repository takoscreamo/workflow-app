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
use App\Domain\Services\NodeProcessorFactory;
use Illuminate\Database\Eloquent\Collection;

class WorkflowUsecase
{
    public function __construct(
        private WorkflowRepositoryInterface $workflowRepository,
        private NodeRepositoryInterface $nodeRepository,
        private NodeProcessorFactory $nodeProcessorFactory
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

        $nodes = $this->nodeRepository->findByWorkflowId($id);
        if ($nodes->isEmpty()) {
            throw new \Exception('ワークフローにノードがありません');
        }

        $results = [];
        $currentInput = null;

        foreach ($nodes as $nodeModel) {
            try {
                $node = $this->toNodeEntity($nodeModel);
                $processor = $this->nodeProcessorFactory->create($node->nodeType);

                $result = $processor->process($node->config, $currentInput);
                $currentInput = $result;

                $results[] = [
                    'node_id' => $node->id,
                    'node_type' => $node->nodeType->value,
                    'config' => $node->config,
                    'result' => $result,
                    'status' => 'success'
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'node_id' => $node->id ?? null,
                    'node_type' => $node->nodeType->value ?? 'unknown',
                    'config' => $node->config ?? [],
                    'result' => null,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
                break; // エラーが発生したら処理を停止
            }
        }

        return [
            'workflow_id' => $id,
            'workflow_name' => $workflow->name,
            'results' => $results,
            'final_result' => $currentInput
        ];
    }

    private function toNodeEntity($nodeModel): Node
    {
        return new Node(
            id: $nodeModel->id,
            workflowId: $nodeModel->workflow_id,
            nodeType: NodeType::from($nodeModel->node_type),
            config: $nodeModel->config,
            createdAt: $nodeModel->created_at,
            updatedAt: $nodeModel->updated_at
        );
    }
}
