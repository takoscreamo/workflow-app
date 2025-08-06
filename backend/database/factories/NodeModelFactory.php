<?php

namespace Database\Factories;

use App\Infrastructure\Models\NodeModel;
use App\Domain\Entities\NodeType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Infrastructure\Models\NodeModel>
 */
class NodeModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Infrastructure\Models\NodeModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_id' => 1,
            'node_type' => NodeType::FORMATTER,
            'config' => [
                'format_type' => 'uppercase',
                'text' => 'hello world'
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
