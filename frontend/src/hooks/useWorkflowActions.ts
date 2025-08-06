import { useState, useEffect } from 'react';
import { api } from '@/lib/api';
import { WorkflowRunResult } from '@/types/workflow';

interface CreateWorkflowData {
  name: string;
  input_type: 'text' | 'pdf';
  output_type: 'text' | 'pdf';
  input_data: string;
}

interface UpdateWorkflowData {
  name: string;
  input_type: 'text' | 'pdf';
  output_type: 'text' | 'pdf';
  input_data: string;
}

interface ExecutionStatus {
  status: 'processing' | 'completed' | 'error' | 'not_found';
  result?: WorkflowRunResult;
  message?: string;
}

interface ExecutionResult {
  type: 'text' | 'pdf';
  result: string;
}

const STORAGE_KEY = 'workflow_execution_results';

export function useWorkflowActions() {
  const [error, setError] = useState<string | null>(null);
  const [executingWorkflows, setExecutingWorkflows] = useState<Set<number>>(new Set());
  const [executionResults, setExecutionResults] = useState<Record<number, ExecutionResult>>({});

  // ローカルストレージから実行結果を復元
  useEffect(() => {
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (stored) {
        const parsed = JSON.parse(stored);
        setExecutionResults(parsed);
      }
    } catch (err) {
      console.error('Failed to load execution results from localStorage:', err);
    }
  }, []);

  // 実行結果をローカルストレージに保存
  const saveExecutionResults = (results: Record<number, ExecutionResult>) => {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(results));
    } catch (err) {
      console.error('Failed to save execution results to localStorage:', err);
    }
  };

  const handleFileUpload = async (file: File): Promise<string> => {
    try {
      const result = await api.uploadFile(file);
      return result.file_path;
    } catch (err) {
      throw new Error('ファイルのアップロードに失敗しました');
    }
  };

  const createWorkflow = async (data: CreateWorkflowData, file: File | null) => {
    try {
      setError(null);
      let inputData = data.input_data;
      
      if (data.input_type === 'pdf' && file) {
        inputData = await handleFileUpload(file);
      }

      const workflow = await api.createWorkflow({
        name: data.name,
        input_type: data.input_type,
        output_type: data.output_type,
        input_data: inputData
      });

      return workflow;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'ワークフローの作成に失敗しました';
      setError(errorMessage);
      throw new Error(errorMessage);
    }
  };

  const updateWorkflow = async (id: number, data: UpdateWorkflowData, file: File | null) => {
    try {
      setError(null);
      let inputData = data.input_data;
      
      if (data.input_type === 'pdf' && file) {
        inputData = await handleFileUpload(file);
      }

      const updatedWorkflow = await api.updateWorkflow(id, {
        name: data.name,
        input_type: data.input_type,
        output_type: data.output_type,
        input_data: inputData
      });

      return updatedWorkflow;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'ワークフローの更新に失敗しました';
      setError(errorMessage);
      throw new Error(errorMessage);
    }
  };

  const deleteWorkflow = async (id: number) => {
    try {
      setError(null);
      await api.deleteWorkflow(id);
      // ワークフロー削除時に実行結果も削除
      const newResults = { ...executionResults };
      delete newResults[id];
      setExecutionResults(newResults);
      saveExecutionResults(newResults);
      
      // 実行中のワークフローからも削除
      setExecutingWorkflows(prev => {
        const newSet = new Set(prev);
        newSet.delete(id);
        return newSet;
      });
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'ワークフローの削除に失敗しました';
      setError(errorMessage);
      throw new Error(errorMessage);
    }
  };

  // 実行状況を監視する関数
  const pollExecutionStatus = async (sessionId: string): Promise<WorkflowRunResult> => {
    return new Promise((resolve, reject) => {
      let attempts = 0;
      const maxAttempts = 60; // 30秒間（0.5秒 × 60回）
      
      const poll = async () => {
        try {
          attempts++;
          
          if (attempts > maxAttempts) {
            reject(new Error('実行タイムアウト: 30秒以内に完了しませんでした'));
            return;
          }
          
          const status = await api.getExecutionStatus(sessionId);
          
          if (status.status === 'completed') {
            resolve(status.result!);
          } else if (status.status === 'error') {
            reject(new Error(status.message || '実行中にエラーが発生しました'));
          } else if (status.status === 'not_found') {
            // まだ処理中の場合、0.5秒後に再試行
            setTimeout(poll, 500);
          } else {
            // まだ処理中の場合、0.5秒後に再試行
            setTimeout(poll, 500);
          }
        } catch (err) {
          // エラーが発生した場合も、0.5秒後に再試行
          setTimeout(poll, 500);
        }
      };
      
      poll();
    });
  };

  const runWorkflow = async (id: number) => {
    try {
      setError(null);
      
      // 実行中のワークフローに追加
      setExecutingWorkflows(prev => new Set([...prev, id]));
      
      // 非同期実行を開始
      const executionResponse = await api.runWorkflow(id);
      console.log('Workflow execution started:', executionResponse);
      
      if (executionResponse.status === 'processing') {
        // 実行状況を監視
        const result = await pollExecutionStatus(executionResponse.session_id);
        
        console.log('Workflow execution completed:', result);
        console.log('Output type:', result.output_type);
        console.log('Final result length:', result.final_result?.length);
        
        let executionResult: ExecutionResult;
        
        if (result.output_type === 'pdf' && result.final_result) {
          console.log('PDF result generated');
          executionResult = { type: 'pdf', result: result.final_result };
        } else if (result.final_result) {
          console.log('Text result generated');
          executionResult = { type: 'text', result: result.final_result };
        } else {
          console.log('No result available');
          executionResult = { type: 'text', result: '結果がありません' };
        }
        
        // 実行結果を保存
        const newResults = {
          ...executionResults,
          [id]: executionResult
        };
        setExecutionResults(newResults);
        saveExecutionResults(newResults);
        
        return executionResult;
      } else {
        throw new Error('ワークフロー実行の開始に失敗しました');
      }
    } catch (err) {
      console.error('Workflow execution error:', err);
      const errorMessage = err instanceof Error ? err.message : 'ワークフローの実行に失敗しました';
      setError(errorMessage);
      throw new Error(errorMessage);
    } finally {
      // 実行中のワークフローから削除
      setExecutingWorkflows(prev => {
        const newSet = new Set(prev);
        newSet.delete(id);
        return newSet;
      });
    }
  };

  const getExecutionResult = (id: number): ExecutionResult | null => {
    return executionResults[id] || null;
  };

  const clearExecutionResult = (id: number) => {
    const newResults = { ...executionResults };
    delete newResults[id];
    setExecutionResults(newResults);
    saveExecutionResults(newResults);
  };

  const isWorkflowExecuting = (id: number): boolean => {
    return executingWorkflows.has(id);
  };

  return {
    error,
    setError,
    executingWorkflows,
    executionResults,
    createWorkflow,
    updateWorkflow,
    deleteWorkflow,
    runWorkflow,
    getExecutionResult,
    clearExecutionResult,
    isWorkflowExecuting
  };
} 