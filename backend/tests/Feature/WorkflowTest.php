<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Infrastructure\Models\WorkflowModel;
use App\Infrastructure\Models\NodeModel;
use App\Domain\Entities\NodeType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class WorkflowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_ワークフローを作成できる()
    {
        $workflowData = [
            'name' => 'テストワークフロー',
            'input_type' => 'text',
            'output_type' => 'pdf'
        ];

        $response = $this->postJson('/api/workflows', $workflowData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'name',
                    'input_type',
                    'output_type',
                    'input_data',
                    'created_at',
                    'updated_at'
                ]);

        $this->assertDatabaseHas('workflows', [
            'name' => 'テストワークフロー'
        ]);
    }

    public function test_ワークフローを取得できる()
    {
        $workflow = WorkflowModel::factory()->create([
            'name' => 'テストワークフロー'
        ]);

        $response = $this->getJson("/api/workflows/{$workflow->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $workflow->id,
                    'name' => 'テストワークフロー'
                ]);
    }

    public function test_ワークフローを更新できる()
    {
        $workflow = WorkflowModel::factory()->create([
            'name' => '元のワークフロー名'
        ]);

        $updateData = [
            'name' => '更新されたワークフロー名',
            'input_type' => 'pdf',
            'output_type' => 'text'
        ];

        $response = $this->putJson("/api/workflows/{$workflow->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'name' => '更新されたワークフロー名'
                ]);

        $this->assertDatabaseHas('workflows', [
            'id' => $workflow->id,
            'name' => '更新されたワークフロー名'
        ]);
    }

    public function test_ワークフローを削除できる()
    {
        $workflow = WorkflowModel::factory()->create();

        $response = $this->deleteJson("/api/workflows/{$workflow->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('workflows', [
            'id' => $workflow->id
        ]);
    }

    public function test_ワークフローにノードを追加できる()
    {
        $workflow = WorkflowModel::factory()->create();

        $nodeData = [
            'node_type' => NodeType::FORMATTER->value,
            'config' => [
                'format_type' => 'uppercase',
                'text' => 'hello world'
            ]
        ];

        $response = $this->postJson("/api/workflows/{$workflow->id}/nodes", $nodeData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'workflow_id',
                    'node_type',
                    'config',
                    'created_at',
                    'updated_at'
                ]);

        $this->assertDatabaseHas('nodes', [
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::FORMATTER->value
        ]);
    }

    public function test_生成AIノードを追加できる()
    {
        $workflow = WorkflowModel::factory()->create();

        $nodeData = [
            'node_type' => NodeType::GENERATIVE_AI->value,
            'config' => [
                'prompt' => 'テストプロンプト',
                'model' => 'gpt-3.5-turbo',
                'temperature' => 0.7,
                'max_tokens' => 100
            ]
        ];

        $response = $this->postJson("/api/workflows/{$workflow->id}/nodes", $nodeData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('nodes', [
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::GENERATIVE_AI->value
        ]);
    }

    public function test_テキスト抽出ノードを追加できる()
    {
        $workflow = WorkflowModel::factory()->create([
            'input_type' => 'pdf'
        ]);

        $nodeData = [
            'node_type' => NodeType::EXTRACT_TEXT->value,
            'config' => [
                'file_path' => '/path/to/file.pdf'
            ]
        ];

        $response = $this->postJson("/api/workflows/{$workflow->id}/nodes", $nodeData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('nodes', [
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::EXTRACT_TEXT->value
        ]);
    }

    public function test_存在しないワークフローに対して404を返す()
    {
        $response = $this->getJson('/api/workflows/999');

        $response->assertStatus(404);
    }

    public function test_無効なワークフローデータでバリデーションエラーを返す()
    {
        $response = $this->postJson('/api/workflows', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    public function test_無効なノードデータでバリデーションエラーを返す()
    {
        $workflow = WorkflowModel::factory()->create();

        $response = $this->postJson("/api/workflows/{$workflow->id}/nodes", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['node_type']);
    }
}
