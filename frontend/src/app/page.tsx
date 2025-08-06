'use client';

import { useState } from 'react';
import { Workflow } from '@/types/workflow';
import { useWorkflows } from '@/hooks/useWorkflows';
import { useWorkflowActions } from '@/hooks/useWorkflowActions';
import { ErrorMessage, LoadingSpinner } from '@/components/common';
import { WorkflowForm, WorkflowList, ExecutionResultModal } from '@/components/workflow';

export default function Home() {
  const { workflows, loading, error: workflowsError, loadWorkflows, setError: setWorkflowsError } = useWorkflows();
  const { 
    createWorkflow, 
    updateWorkflow, 
    deleteWorkflow, 
    runWorkflow, 
    getExecutionResult,
    clearExecutionResult,
    error: actionsError, 
    setError: setActionsError, 
    executingWorkflows,
    executionResults
  } = useWorkflowActions();
  
  const [editingWorkflow, setEditingWorkflow] = useState<number | null>(null);
  const [showAddNode, setShowAddNode] = useState<number | null>(null);
  const [executionResult, setExecutionResult] = useState<string | null>(null);
  const [executionResultType, setExecutionResultType] = useState<'text' | 'pdf'>('text');
  const [executionWorkflowId, setExecutionWorkflowId] = useState<number | null>(null);
  const [showExecutionResult, setShowExecutionResult] = useState(false);

  // エラー状態を統合
  const error = workflowsError || actionsError;
  const setError = (message: string | null) => {
    setWorkflowsError(message);
    setActionsError(message);
  };

  const handleCreateWorkflow = async (data: { name: string; input_type: 'text' | 'pdf'; output_type: 'text' | 'pdf'; input_data: string }, file: File | null) => {
    try {
      await createWorkflow(data, file);
      await loadWorkflows();
    } catch (error) {
      // エラーはuseWorkflowActionsで処理済み
    }
  };

  const handleUpdateWorkflow = async (workflow: Workflow, file: File | null) => {
    console.log('handleUpdateWorkflow called with:', {
      workflowId: workflow.id,
      name: workflow.name,
      input_type: workflow.input_type,
      output_type: workflow.output_type,
      input_data: workflow.input_data,
      input_data_length: workflow.input_data?.length
    });
    
    try {
      console.log('Calling updateWorkflow...');
      const result = await updateWorkflow(workflow.id, {
        name: workflow.name,
        input_type: workflow.input_type,
        output_type: workflow.output_type,
        input_data: workflow.input_data || ''
      }, file);
      
      console.log('updateWorkflow result:', result);
      console.log('Reloading workflows...');
      await loadWorkflows();
      console.log('Setting editingWorkflow to null...');
      setEditingWorkflow(null);
      console.log('handleUpdateWorkflow completed successfully');
    } catch (error) {
      console.error('handleUpdateWorkflow error:', error);
      // エラーを再スローして、WorkflowItemでキャッチできるようにする
      throw error;
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
      // テキストとPDFの両方とも、結果表示ボタンでのみ表示する
      // 自動的なモーダル表示は行わない
    } catch (error) {
      // エラーはuseWorkflowActionsで処理済み
    }
  };

  const handleShowResult = (id: number) => {
    const result = getExecutionResult(id);
    if (result) {
      setExecutionResult(result.result);
      setExecutionResultType(result.type);
      setExecutionWorkflowId(id);
      setShowExecutionResult(true);
    }
  };

  const handleAddNode = (workflowId: number) => {
    setShowAddNode(showAddNode === workflowId ? null : workflowId);
  };

  const handleNodeDeleted = async () => {
    await loadWorkflows();
  };

  const handleEditWorkflow = (workflow: Workflow, file: File | null = null) => {
    setEditingWorkflow(editingWorkflow === workflow.id ? null : workflow.id);
  };

  const handleCancelEdit = () => {
    setEditingWorkflow(null);
  };

  const handleError = (errorMessage: string) => {
    setError(errorMessage);
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
          resultType={executionResultType}
          workflowId={executionWorkflowId || undefined}
        />

        <WorkflowList
          workflows={workflows}
          onEdit={handleEditWorkflow}
          onSave={handleUpdateWorkflow}
          onDelete={handleDeleteWorkflow}
          onRun={handleRunWorkflow}
          onAddNode={handleAddNode}
          onShowResult={handleShowResult}
          onNodeDeleted={handleNodeDeleted}
          onCancel={handleCancelEdit}
          onError={handleError}
          editingWorkflow={editingWorkflow}
          showAddNode={showAddNode}
          executingWorkflows={executingWorkflows}
          executionResults={executionResults}
        />
      </div>
    </div>
  );
}
