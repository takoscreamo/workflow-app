<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Workflow;
use App\Domain\Repositories\WorkflowRepositoryInterface;
use App\Infrastructure\Models\WorkflowModel;

class WorkflowRepository implements WorkflowRepositoryInterface
{
    public function findAll(): array
    {
        $models = WorkflowModel::with('nodes')->get();
        return $models->map(fn($model) => $this->toEntity($model))->toArray();
    }

    public function findById(int $id): ?Workflow
    {
        $model = WorkflowModel::with('nodes')->find($id);
        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function save(Workflow $workflow): Workflow
    {
        if ($workflow->id) {
            $model = WorkflowModel::find($workflow->id);
            if (!$model) {
                throw new \Exception('ワークフローが見つかりません');
            }
            $model->name = $workflow->name;
            $model->input_type = $workflow->inputType;
            $model->output_type = $workflow->outputType;
            $model->input_data = $workflow->inputData;
            $model->updated_at = $workflow->updatedAt;
        } else {
            $model = new WorkflowModel();
            $model->name = $workflow->name;
            $model->input_type = $workflow->inputType;
            $model->output_type = $workflow->outputType;
            $model->input_data = $workflow->inputData;
            $model->created_at = $workflow->createdAt;
            $model->updated_at = $workflow->updatedAt;
        }

        $model->save();

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        WorkflowModel::destroy($id);
    }

    private function toEntity(WorkflowModel $model): Workflow
    {
        $nodes = [];
        if ($model->nodes && $model->nodes instanceof \Illuminate\Database\Eloquent\Collection) {
            $nodes = $model->nodes->toArray();
        }

        return new Workflow(
            id: $model->id,
            name: $model->name,
            inputType: $model->input_type,
            outputType: $model->output_type,
            inputData: $model->input_data,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            nodes: $nodes
        );
    }
}
