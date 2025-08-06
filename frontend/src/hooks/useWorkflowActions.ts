import { useState } from 'react';
import { api } from '@/lib/api';

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

export function useWorkflowActions() {
  const [error, setError] = useState<string | null>(null);

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
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'ワークフローの削除に失敗しました';
      setError(errorMessage);
      throw new Error(errorMessage);
    }
  };

  const runWorkflow = async (id: number) => {
    try {
      setError(null);
      const result = await api.runWorkflow(id);
      
      console.log('Workflow execution result:', result);
      console.log('Output type:', result.output_type);
      console.log('Final result length:', result.final_result?.length);
      
      if (result.output_type === 'pdf' && result.final_result) {
        console.log('Downloading PDF...');
        await api.downloadPdf(result.final_result, `workflow_result_${id}.pdf`);
        console.log('PDF download completed');
        return { type: 'pdf', result: result.final_result };
      } else if (result.final_result) {
        console.log('Showing text result in modal');
        return { type: 'text', result: result.final_result };
      } else {
        console.log('No result available');
        return { type: 'text', result: '結果がありません' };
      }
    } catch (err) {
      console.error('Workflow execution error:', err);
      const errorMessage = err instanceof Error ? err.message : 'ワークフローの実行に失敗しました';
      setError(errorMessage);
      throw new Error(errorMessage);
    }
  };

  return {
    error,
    setError,
    createWorkflow,
    updateWorkflow,
    deleteWorkflow,
    runWorkflow
  };
} 