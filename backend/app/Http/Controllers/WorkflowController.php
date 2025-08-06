<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\Node;
use App\NodeType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkflowController extends Controller
{
    /**
     * ワークフロー一覧を取得
     */
    public function index(): JsonResponse
    {
        $workflows = Workflow::with('nodes')->get();
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

        $workflow = Workflow::create([
            'name' => $request->name,
        ]);

        // ノードリレーションを読み込んで返す
        $workflow->load('nodes');

        return response()->json($workflow, 201);
    }

    /**
     * 指定されたワークフローを取得
     */
    public function show(string $id): JsonResponse
    {
        $workflow = Workflow::with('nodes')->findOrFail($id);
        return response()->json($workflow);
    }

    /**
     * ワークフローにノードを追加
     */
    public function addNode(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'node_type' => 'required|string|in:' . implode(',', array_column(NodeType::cases(), 'value')),
            'config' => 'required|array',
        ]);

        $workflow = Workflow::findOrFail($id);

        $node = Node::create([
            'workflow_id' => $workflow->id,
            'node_type' => $request->node_type,
            'config' => $request->config,
        ]);

        return response()->json($node, 201);
    }

    /**
     * ワークフローを実行（非同期処理の準備）
     */
    public function run(string $id): JsonResponse
    {
        $workflow = Workflow::with('nodes')->findOrFail($id);

        // TODO: 非同期処理を実装
        // 現在は同期的に実行
        $result = $this->executeWorkflow($workflow);

        return response()->json([
            'message' => 'ワークフローが実行されました',
            'result' => $result,
        ]);
    }

    /**
     * ワークフローを更新
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $workflow = Workflow::findOrFail($id);
        $workflow->update([
            'name' => $request->name,
        ]);

        // ノードリレーションを読み込んで返す
        $workflow->load('nodes');

        return response()->json($workflow);
    }

    /**
     * ワークフローを削除
     */
    public function destroy(string $id): JsonResponse
    {
        $workflow = Workflow::findOrFail($id);
        $workflow->delete();

        return response()->json(['message' => 'ワークフローが削除されました']);
    }

    /**
     * ワークフローを実行（同期的な実装）
     */
    private function executeWorkflow(Workflow $workflow): array
    {
        $result = [];
        $input = '';

        foreach ($workflow->nodes as $node) {
            switch ($node->node_type) {
                case NodeType::EXTRACT_TEXT->value:
                    // TODO: PDFテキスト抽出処理
                    $input = "PDFから抽出されたテキスト";
                    break;

                case NodeType::GENERATIVE_AI->value:
                    // TODO: AI生成処理
                    $input = "AIが生成したテキスト";
                    break;

                case NodeType::FORMATTER->value:
                    // TODO: テキスト整形処理
                    $input = strtoupper($input);
                    break;
            }

            $result[] = [
                'node_id' => $node->id,
                'node_type' => $node->node_type,
                'output' => $input,
            ];
        }

        return $result;
    }
}
