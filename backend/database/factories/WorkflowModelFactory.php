<?php

namespace Database\Factories;

use App\Infrastructure\Models\WorkflowModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Infrastructure\Models\WorkflowModel>
 */
class WorkflowModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Infrastructure\Models\WorkflowModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'input_type' => 'text',
            'output_type' => 'pdf',
            'input_data' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
