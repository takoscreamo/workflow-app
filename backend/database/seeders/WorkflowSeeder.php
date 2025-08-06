<?php

namespace Database\Seeders;

use App\Infrastructure\Models\WorkflowModel;
use App\Infrastructure\Models\NodeModel;
use App\Domain\Entities\NodeType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // サンプルワークフローを作成
        $workflow = WorkflowModel::create([
            'name' => 'サンプルワークフロー',
        ]);

        // サンプルノードを作成
        NodeModel::create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::EXTRACT_TEXT->value,
            'config' => [
                'description' => 'PDFからテキストを抽出',
            ],
        ]);

        NodeModel::create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::GENERATIVE_AI->value,
            'config' => [
                'prompt' => '以下のテキストを要約してください：',
                'model' => 'google/gemma-3n-e2b-it:free',
                'max_tokens' => 1000,
            ],
        ]);

        NodeModel::create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::FORMATTER->value,
            'config' => [
                'format_type' => 'uppercase',
                'description' => 'テキストを大文字に変換',
            ],
        ]);
    }
}
