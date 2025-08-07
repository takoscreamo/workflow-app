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
            'name' => 'テキスト小文字変換ワークフロー',
            'input_type' => 'text',
            'output_type' => 'text',
            'input_data' => 'Hello World!',
        ]);

        // サンプルノードを作成
        NodeModel::create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::FORMATTER->value,
            'config' => [
                'format_type' => 'uppercase',
                'description' => 'テキストを大文字に変換',
            ],
        ]);

        NodeModel::create([
            'workflow_id' => $workflow->id,
            'node_type' => NodeType::FORMATTER->value,
            'config' => [
                'format_type' => 'fullwidth',
                'description' => 'テキストを全角に変換',
            ],
        ]);
    }
}
