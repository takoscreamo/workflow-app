<?php

namespace App\Http\Controllers;

use App\Usecase\DTOs\CreateWorkflowDTO;
use App\Usecase\DTOs\UpdateWorkflowDTO;
use App\Usecase\DTOs\AddNodeDTO;
use App\Usecase\WorkflowUsecase;
use App\Domain\Entities\NodeType;
use App\Jobs\RunWorkflowJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
            'input_type' => 'sometimes|string|in:text,pdf',
            'output_type' => 'sometimes|string|in:text,pdf',
            'input_data' => 'sometimes|nullable|string',
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
     * ワークフローを非同期実行
     */
    public function runWorkflow(string $id): JsonResponse
    {
        try {
            // セッションIDを生成（実行状況の監視用）
            $sessionId = 'workflow_' . $id . '_' . Str::random(10);

            // 非同期ジョブをディスパッチ
            RunWorkflowJob::dispatch((int) $id, $sessionId);

            return response()->json([
                'message' => 'ワークフロー実行を開始しました',
                'session_id' => $sessionId,
                'status' => 'processing'
            ], 202);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * ワークフロー実行状況を取得
     */
    public function getExecutionStatus(string $sessionId): JsonResponse
    {
        $result = DB::table('execution_results')
            ->where('session_id', $sessionId)
            ->first();

        if (!$result) {
            return response()->json([
                'status' => 'not_found',
                'message' => '実行状況が見つかりません'
            ], 404);
        }

        $resultData = json_decode($result->result, true);

        if ($result->status === 'error' || (isset($resultData['error']) && $resultData['error'])) {
            return response()->json([
                'status' => 'error',
                'message' => $resultData['message'] ?? '実行中にエラーが発生しました'
            ], 400);
        }

        return response()->json([
            'status' => 'completed',
            'result' => $resultData
        ]);
    }

    /**
     * ワークフローを更新
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'input_type' => 'sometimes|string|in:text,pdf',
            'output_type' => 'sometimes|string|in:text,pdf',
            'input_data' => 'sometimes|nullable|string',
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
