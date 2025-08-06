<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Node;
use App\Domain\Repositories\NodeRepositoryInterface;
use App\Infrastructure\Models\NodeModel;
use Illuminate\Database\Eloquent\Collection;

class NodeRepository implements NodeRepositoryInterface
{
    public function findByWorkflowId(int $workflowId): Collection
    {
        return NodeModel::where('workflow_id', $workflowId)->get();
    }

    public function save(Node $node): Node
    {
        if ($node->id) {
            $model = NodeModel::find($node->id);
            if (!$model) {
                throw new \Exception('ノードが見つかりません');
            }
            $model->workflow_id = $node->workflowId;
            $model->node_type = $node->nodeType->value;
            $model->config = $node->config;
            $model->updated_at = $node->updatedAt;
        } else {
            $model = new NodeModel();
            $model->workflow_id = $node->workflowId;
            $model->node_type = $node->nodeType->value;
            $model->config = $node->config;
            $model->created_at = $node->createdAt;
            $model->updated_at = $node->updatedAt;
        }

        $model->save();

        return $this->toEntity($model);
    }

    public function deleteByWorkflowId(int $workflowId): void
    {
        NodeModel::where('workflow_id', $workflowId)->delete();
    }

    private function toEntity(NodeModel $model): Node
    {
        return new Node(
            id: $model->id,
            workflowId: $model->workflow_id,
            nodeType: \App\Domain\Entities\NodeType::from($model->node_type),
            config: $model->config,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }
}
