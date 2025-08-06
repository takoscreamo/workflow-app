'use client';

import { useState } from 'react';
import { Workflow } from '@/types/workflow';
import { useWorkflows } from '@/hooks/useWorkflows';
import { useWorkflowActions } from '@/hooks/useWorkflowActions';
import { ErrorMessage, LoadingSpinner } from '@/components/common';
import { WorkflowForm, WorkflowList, ExecutionResultModal } from '@/components/workflow';

export default function Home() {
  const { workflows, loading, error, loadWorkflows, setError } = useWorkflows();
  const { createWorkflow, updateWorkflow, deleteWorkflow, runWorkflow } = useWorkflowActions();
  
  const [editingWorkflow, setEditingWorkflow] = useState<number | null>(null);
  const [showAddNode, setShowAddNode] = useState<number | null>(null);
  const [executionResult, setExecutionResult] = useState<string | null>(null);
  const [showExecutionResult, setShowExecutionResult] = useState(false);

  const handleCreateWorkflow = async (data: { name: string; input_type: 'text' | 'pdf'; output_type: 'text' | 'pdf'; input_data: string }, file: File | null) => {
    try {
      await createWorkflow(data, file);
      await loadWorkflows();
    } catch (error) {
      // エラーはuseWorkflowActionsで処理済み
    }
  };

  const handleUpdateWorkflow = async (workflow: Workflow) => {
    try {
      await updateWorkflow(workflow.id, {
        name: workflow.name,
        input_type: workflow.input_type,
        output_type: workflow.output_type,
        input_data: workflow.input_data || ''
      }, null);
      await loadWorkflows();
      setEditingWorkflow(null);
    } catch (error) {
      // エラーはuseWorkflowActionsで処理済み
    }
  };

  const handleDeleteWorkflow = async (id: number) => {
    if (!confirm('このワークフローを削除しますか？')) return;

    try {
      await deleteWorkflow(id);
      await loadWorkflows();
    } catch (error) {
      // エラーはuseWorkflowActionsで処理済み
    }
  };

  const handleRunWorkflow = async (id: number) => {
    try {
      const result = await runWorkflow(id);
      if (result.type === 'text') {
        setExecutionResult(result.result);
        setShowExecutionResult(true);
      }
    } catch (error) {
      // エラーはuseWorkflowActionsで処理済み
    }
  };

  const handleAddNode = (workflowId: number) => {
    setShowAddNode(showAddNode === workflowId ? null : workflowId);
  };

  const handleEditWorkflow = (workflow: Workflow) => {
    setEditingWorkflow(editingWorkflow === workflow.id ? null : workflow.id);
  };

  if (loading) {
    return <LoadingSpinner />;
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">
          ワークフローアプリケーション
        </h1>

        <ErrorMessage message={error} onClose={() => setError(null)} />

        <WorkflowForm onSubmit={handleCreateWorkflow} />

        <ExecutionResultModal
          isOpen={showExecutionResult}
          onClose={() => setShowExecutionResult(false)}
          result={executionResult}
        />

        <WorkflowList
          workflows={workflows}
          onEdit={handleEditWorkflow}
          onDelete={handleDeleteWorkflow}
          onRun={handleRunWorkflow}
          onAddNode={handleAddNode}
          editingWorkflow={editingWorkflow}
          showAddNode={showAddNode}
        />
      </div>
    </div>
  );
}
