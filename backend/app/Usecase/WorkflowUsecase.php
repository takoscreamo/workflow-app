<?php

namespace App\Usecase;

use Illuminate\Support\Facades\Log;

use App\Usecase\DTOs\CreateWorkflowDTO;
use App\Usecase\DTOs\UpdateWorkflowDTO;
use App\Usecase\DTOs\AddNodeDTO;
use App\Domain\Entities\Workflow;
use App\Domain\Entities\Node;
use App\Domain\Entities\NodeType;
use App\Domain\Repositories\WorkflowRepositoryInterface;
use App\Domain\Repositories\NodeRepositoryInterface;
use App\Domain\Services\NodeProcessorFactory;
use App\Domain\Services\PdfGeneratorService;
use Illuminate\Database\Eloquent\Collection;

class WorkflowUsecase
{
    public function __construct(
        private WorkflowRepositoryInterface $workflowRepository,
        private NodeRepositoryInterface $nodeRepository,
        private NodeProcessorFactory $nodeProcessorFactory,
        private PdfGeneratorService $pdfGeneratorService
    ) {
        Log::info("WorkflowUsecaseコンストラクタ呼び出し", [
            'workflow_repository_class' => get_class($workflowRepository),
            'node_repository_class' => get_class($nodeRepository),
            'node_processor_factory_class' => get_class($nodeProcessorFactory),
            'pdf_generator_service_class' => get_class($pdfGeneratorService)
        ]);
    }

    public function getAllWorkflows(): array
    {
        return $this->workflowRepository->findAll();
    }

    public function getWorkflowById(int $id): ?Workflow
    {
        return $this->workflowRepository->findById($id);
    }

    public function createWorkflow(CreateWorkflowDTO $dto): Workflow
    {
        $workflow = Workflow::create($dto->name, $dto->inputType, $dto->outputType, $dto->inputData);
        return $this->workflowRepository->save($workflow);
    }

    public function updateWorkflow(UpdateWorkflowDTO $dto): ?Workflow
    {
        $workflow = $this->workflowRepository->findById($dto->id);
        if (!$workflow) {
            return null;
        }

        $updatedWorkflow = $workflow->updateInputOutputConfig($dto->inputType, $dto->outputType, $dto->inputData);
        $updatedWorkflow = $updatedWorkflow->updateName($dto->name);
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
        // ワークフローを取得
        $workflow = $this->workflowRepository->findById($dto->workflowId);
        if (!$workflow) {
            throw new \Exception('ワークフローが見つかりません');
        }

        // 既存のノードを取得してワークフローに設定
        $existingNodes = $this->nodeRepository->findByWorkflowId($dto->workflowId);
        $workflowWithNodes = $workflow->withNodes($existingNodes);

        // ドメインルールを検証
        $workflowWithNodes->validateNodeAddition($dto->nodeType);

        $node = Node::create($dto->workflowId, $dto->nodeType, $dto->config);
        return $this->nodeRepository->save($node);
    }

    public function deleteNode(int $nodeId): bool
    {
        return $this->nodeRepository->deleteById($nodeId);
    }

    public function getWorkflowNodes(int $workflowId): array
    {
        return $this->nodeRepository->findByWorkflowId($workflowId);
    }

    public function runWorkflow(int $id): array
    {
        Log::info("runWorkflow開始", ['workflow_id' => $id]);

        Log::info("runWorkflow開始直後", [
            'workflow_id' => $id,
            'workflow_repository_class' => get_class($this->workflowRepository)
        ]);

        try {
            $workflow = $this->workflowRepository->findById($id);
            if (!$workflow) {
                throw new \Exception('ワークフローが見つかりません');
            }
        } catch (\Exception $e) {
            Log::error("ワークフロー取得エラー", [
                'workflow_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        Log::info("ワークフロー取得完了", [
            'workflow_id' => $workflow->id,
            'workflow_name' => $workflow->name,
            'input_type' => $workflow->inputType,
            'input_data' => $workflow->inputData
        ]);

        try {
            $nodes = $this->nodeRepository->findByWorkflowId($id);
            if (empty($nodes)) {
                throw new \Exception('ワークフローにノードがありません');
            }
        } catch (\Exception $e) {
            Log::error("ノード取得エラー", [
                'workflow_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        Log::info("ノード取得完了", [
            'node_count' => count($nodes),
            'nodes' => collect($nodes)->map(function($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->nodeType->value,
                    'class' => get_class($n)
                ];
            })->toArray()
        ]);

        Log::info("foreachループ開始前", [
            'node_count' => count($nodes),
            'workflow_input_data' => $workflow->inputData
        ]);

        $results = [];
        $currentInput = $workflow->inputData; // ワークフローの初期入力データを使用

        Log::info("foreachループ開始", [
            'node_count' => count($nodes)
        ]);

        foreach ($nodes as $node) {
            try {
                Log::info("ノード処理開始", [
                    'node_id' => $node->id,
                    'node_type' => $node->nodeType->value,
                    'input' => $currentInput
                ]);

                Log::info("NodeProcessorFactory.create呼び出し前", [
                    'node_type' => $node->nodeType->value,
                    'factory_class' => get_class($this->nodeProcessorFactory)
                ]);

                $processor = $this->nodeProcessorFactory->create($node->nodeType);

                Log::info("processor.process呼び出し前", [
                    'node_id' => $node->id,
                    'node_type' => $node->nodeType->value,
                    'processor_class' => get_class($processor)
                ]);

                $result = $processor->process($node->config, $currentInput);
                $currentInput = $result;

                Log::info("ノード処理成功", [
                    'node_id' => $node->id,
                    'node_type' => $node->nodeType->value,
                    'result_length' => strlen($result)
                ]);

                $results[] = [
                    'node_id' => $node->id,
                    'node_type' => $node->nodeType->value,
                    'config' => $node->config,
                    'result' => $result,
                    'status' => 'success'
                ];
            } catch (\Exception $e) {
                Log::error("ノード処理エラー", [
                    'node_id' => $node->id ?? null,
                    'node_type' => $node->nodeType->value ?? 'unknown',
                    'node_class' => get_class($node),
                    'node_properties' => get_object_vars($node),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

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

        // PDF出力の場合、Base64エンコードされたデータを返す
        $finalResult = $currentInput;
        if ($workflow->outputType === 'pdf' && $finalResult) {
            // テキストをPDFに変換
            $finalResult = $this->pdfGeneratorService->generatePdfFromText($finalResult);
        }

        return [
            'workflow_id' => $id,
            'workflow_name' => $workflow->name,
            'input_type' => $workflow->inputType,
            'output_type' => $workflow->outputType,
            'results' => $results,
            'final_result' => $finalResult
        ];
    }

    private function toNodeEntity($nodeModel): Node
    {
        Log::info("toNodeEntity呼び出し", [
            'node_model_id' => $nodeModel->id,
            'node_model_workflow_id' => $nodeModel->workflow_id,
            'node_model_node_type' => $nodeModel->node_type,
            'node_model_config' => $nodeModel->config
        ]);

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
