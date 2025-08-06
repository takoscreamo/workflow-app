<?php

namespace App\Http\Controllers;

use App\Usecase\DTOs\CreateWorkflowDTO;
use App\Usecase\DTOs\UpdateWorkflowDTO;
use App\Usecase\DTOs\AddNodeDTO;
use App\Usecase\WorkflowUsecase;
use App\Domain\Entities\NodeType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkflowController extends Controller
{
    public function __construct(
        private WorkflowUsecase $workflowUsecase
    ) {}

    /**
     * ワークフロー一覧を取得
     */
    public function index(): JsonResponse
    {
        $workflows = $this->workflowUsecase->getAllWorkflows();
        return response()->json($workflows);
    }

    /**
     * 新しいワークフローを作成
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $dto = CreateWorkflowDTO::fromRequest($request->all());
        $workflow = $this->workflowUsecase->createWorkflow($dto);

        return response()->json($workflow, 201);
    }

    /**
     * 指定されたワークフローを取得
     */
    public function show(string $id): JsonResponse
    {
        $workflow = $this->workflowUsecase->getWorkflowById((int) $id);
        if (!$workflow) {
            return response()->json(['message' => 'ワークフローが見つかりません'], 404);
        }

        return response()->json($workflow);
    }

    /**
     * ワークフローにノードを追加
     */
    public function addNode(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'node_type' => 'required|string|in:' . implode(',', array_column(NodeType::cases(), 'value')),
                'config' => 'required|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
        }

        $dto = AddNodeDTO::fromRequest((int) $id, $request->all());
        $node = $this->workflowUsecase->addNode($dto);

        return response()->json($node, 201);
    }

    /**
     * ワークフローを実行（非同期処理の準備）
     */
    public function runWorkflow(string $id): JsonResponse
    {
        try {
            $result = $this->workflowUsecase->runWorkflow((int) $id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * ワークフローを更新
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $dto = UpdateWorkflowDTO::fromRequest((int) $id, $request->all());
        $workflow = $this->workflowUsecase->updateWorkflow($dto);

        if (!$workflow) {
            return response()->json(['message' => 'ワークフローが見つかりません'], 404);
        }

        return response()->json($workflow);
    }

    /**
     * ワークフローを削除
     */
    public function destroy(string $id): JsonResponse
    {
        $success = $this->workflowUsecase->deleteWorkflow((int) $id);

        if (!$success) {
            return response()->json(['message' => 'ワークフローが見つかりません'], 404);
        }

        return response()->json(['message' => 'ワークフローが削除されました']);
    }
}
