<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Infrastructure\Models\WorkflowModel;
use App\Infrastructure\Models\NodeModel;
use App\Domain\Entities\NodeType;
use App\Jobs\RunWorkflowJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AsyncWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_ワークフロー実行を開始できる()
    {
        $workflow = WorkflowModel::factory()->create([
            'name' => 'テストワークフロー'
        ]);

        $node = NodeModel::factory()->create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::FORMATTER,
            'config' => [
                'format_type' => 'uppercase',
                'text' => 'hello world'
            ]
        ]);

        $response = $this->postJson("/api/workflows/{$workflow->id}/run");

        $response->assertStatus(202)
                ->assertJsonStructure([
                    'session_id',
                    'status',
                    'message'
                ]);

        Queue::assertPushed(RunWorkflowJob::class);
    }

    public function test_実行状況を取得できる()
    {
        $workflow = WorkflowModel::factory()->create();

        $node = NodeModel::factory()->create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::FORMATTER,
            'config' => [
                'format_type' => 'uppercase',
                'text' => 'hello world'
            ]
        ]);

        $sessionId = 'test-session-' . uniqid();

        // 実行結果をデータベースに保存
        DB::table('execution_results')->insert([
            'session_id' => $sessionId,
            'workflow_id' => $workflow->id,
            'result' => json_encode(['output' => 'HELLO WORLD']),
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson("/api/workflows/execution/{$sessionId}");

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'completed',
                    'result' => ['output' => 'HELLO WORLD']
                ]);
    }

    public function test_存在しないセッションに対して404を返す()
    {
        $response = $this->getJson('/api/workflows/execution/nonexistent-session');

        $response->assertStatus(404);
    }

    public function test_フォーマッターノードでワークフローを実行できる()
    {
        $workflow = WorkflowModel::factory()->create();

        $node = NodeModel::factory()->create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::FORMATTER,
            'config' => [
                'format_type' => 'uppercase',
                'text' => 'hello world'
            ]
        ]);

        $response = $this->postJson("/api/workflows/{$workflow->id}/run");

        $response->assertStatus(202);

        Queue::assertPushed(RunWorkflowJob::class, function ($job) use ($workflow) {
            // リフレクションを使用してprivateプロパティにアクセス
            $reflection = new \ReflectionClass($job);
            $workflowIdProperty = $reflection->getProperty('workflowId');
            $workflowIdProperty->setAccessible(true);
            return $workflowIdProperty->getValue($job) === $workflow->id;
        });
    }

    public function test_生成AIノードでワークフローを実行できる()
    {
        $workflow = WorkflowModel::factory()->create();

        $node = NodeModel::factory()->create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::GENERATIVE_AI,
            'config' => [
                'prompt' => 'テストプロンプト',
                'model' => 'google/gemma-3n-e2b-it:free',
                'temperature' => 0.7
            ]
        ]);

        $response = $this->postJson("/api/workflows/{$workflow->id}/run");

        $response->assertStatus(202);

        Queue::assertPushed(RunWorkflowJob::class);
    }

    public function test_テキスト抽出ノードでワークフローを実行できる()
    {
        $workflow = WorkflowModel::factory()->create();

        $node = NodeModel::factory()->create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::EXTRACT_TEXT,
            'config' => [
                'file_path' => '/path/to/file.pdf'
            ]
        ]);

        $response = $this->postJson("/api/workflows/{$workflow->id}/run");

        $response->assertStatus(202);

        Queue::assertPushed(RunWorkflowJob::class);
    }

    public function test_複数のノードでワークフローを実行できる()
    {
        $workflow = WorkflowModel::factory()->create();

        // 複数のノードを作成
        NodeModel::factory()->create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::EXTRACT_TEXT,
            'config' => ['file_path' => '/path/to/file.pdf']
        ]);

        NodeModel::factory()->create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::FORMATTER,
            'config' => [
                'format_type' => 'uppercase',
                'text' => 'extracted text'
            ]
        ]);

        NodeModel::factory()->create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::GENERATIVE_AI,
            'config' => [
                'prompt' => 'Process this text',
                'model' => 'google/gemma-3n-e2b-it:free'
            ]
        ]);

        $response = $this->postJson("/api/workflows/{$workflow->id}/run");

        $response->assertStatus(202);

        Queue::assertPushed(RunWorkflowJob::class);
    }

    public function test_ノードがないワークフローでエラーを返す()
    {
        $workflow = WorkflowModel::factory()->create();

        $response = $this->postJson("/api/workflows/{$workflow->id}/run");

        $response->assertStatus(400)
                ->assertJson([
                    'error' => 'ワークフローにノードが存在しません'
                ]);

        Queue::assertNotPushed(RunWorkflowJob::class);
    }

    public function test_存在しないワークフローでエラーを返す()
    {
        $response = $this->postJson('/api/workflows/999/run');

        $response->assertStatus(404);
    }

    public function test_実行状況が進捗を更新する()
    {
        $workflow = WorkflowModel::factory()->create();

        $node = NodeModel::factory()->create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::FORMATTER,
            'config' => [
                'format_type' => 'uppercase',
                'text' => 'hello world'
            ]
        ]);

        $sessionId = 'test-session-' . uniqid();

        // 実行中の状態をデータベースに保存
        DB::table('execution_results')->insert([
            'session_id' => $sessionId,
            'workflow_id' => $workflow->id,
            'result' => json_encode(['status' => 'processing', 'progress' => 50]),
            'status' => 'processing',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 実行状況を確認
        $statusResponse = $this->getJson("/api/workflows/execution/{$sessionId}");

        $statusResponse->assertStatus(200)
                     ->assertJson([
                         'status' => 'completed',
                         'result' => [
                             'status' => 'processing',
                             'progress' => 50
                         ]
                     ]);
    }

    public function test_実行タイムアウトを処理する()
    {
        $workflow = WorkflowModel::factory()->create();

        $node = NodeModel::factory()->create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::GENERATIVE_AI,
            'config' => [
                'prompt' => 'テストプロンプト',
                'model' => 'google/gemma-3n-e2b-it:free'
            ]
        ]);

        $sessionId = 'test-session-' . uniqid();

        // タイムアウト状態をデータベースに保存
        DB::table('execution_results')->insert([
            'session_id' => $sessionId,
            'workflow_id' => $workflow->id,
            'result' => json_encode(['error' => true, 'message' => '実行がタイムアウトしました']),
            'status' => 'error',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $statusResponse = $this->getJson("/api/workflows/execution/{$sessionId}");

        $statusResponse->assertStatus(400)
                     ->assertJson([
                         'status' => 'error',
                         'message' => '実行がタイムアウトしました'
                     ]);
    }
}
