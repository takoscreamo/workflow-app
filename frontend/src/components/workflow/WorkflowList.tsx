import { Workflow } from '@/types/workflow';
import { WorkflowItem } from '@/components/workflow/WorkflowItem';

interface WorkflowListProps {
  workflows: Workflow[];
  onEdit: (workflow: Workflow, file: File | null) => void;
  onDelete: (id: number) => void;
  onRun: (id: number) => void;
  onAddNode: (id: number) => void;
  onShowResult: (id: number) => void;
  onNodeDeleted: () => void;
  onCancel?: () => void;
  onError?: (error: string) => void;
  editingWorkflow: number | null;
  showAddNode: number | null;
  executingWorkflows: Set<number>;
  executionResults: Record<number, { type: 'text' | 'pdf'; result: string }>;
}

export function WorkflowList({ 
  workflows, 
  onEdit, 
  onDelete, 
  onRun, 
  onAddNode,
  onShowResult,
  onNodeDeleted,
  onCancel,
  onError,
  editingWorkflow,
  showAddNode,
  executingWorkflows,
  executionResults
}: WorkflowListProps) {
  return (
    <div className="bg-white rounded-lg shadow">
      <div className="px-6 py-4 border-b border-gray-200">
        <h2 className="text-xl font-semibold">ワークフロー一覧</h2>
      </div>
      <div className="divide-y divide-gray-200">
        {workflows.length === 0 ? (
          <div className="px-6 py-8 text-center text-gray-500">
            ワークフローがありません
          </div>
        ) : (
          workflows.map((workflow) => (
            <WorkflowItem
              key={workflow.id}
              workflow={workflow}
              onEdit={onEdit}
              onDelete={onDelete}
              onRun={onRun}
              onAddNode={onAddNode}
              onShowResult={onShowResult}
              onNodeDeleted={onNodeDeleted}
              onCancel={onCancel}
              onError={onError}
              isEditing={editingWorkflow === workflow.id}
              showAddNode={showAddNode === workflow.id}
              isExecuting={executingWorkflows.has(workflow.id)}
              executionResults={executionResults}
            />
          ))
        )}
      </div>
    </div>
  );
} 