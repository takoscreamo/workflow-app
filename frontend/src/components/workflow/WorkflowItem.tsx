import { useState, useEffect } from 'react';
import { Workflow } from '@/types/workflow';
import { Button } from '@/components/common/Button';
import { InputField } from '@/components/forms/InputField';
import { SelectField } from '@/components/forms/SelectField';
import { TextareaField } from '@/components/forms/TextareaField';
import { FileUpload } from '@/components/forms/FileUpload';
import { NodeForm } from '@/components/node/NodeForm';
import { NodeList } from '@/components/node/NodeList';

interface WorkflowItemProps {
  workflow: Workflow;
  onEdit: (workflow: Workflow, file: File | null) => void;
  onSave: (workflow: Workflow, file: File | null) => void;
  onDelete: (id: number) => void;
  onRun: (id: number) => void;
  onAddNode: (id: number) => void;
  onShowResult: (id: number) => void;
  onNodeDeleted: () => void;
  onNodeAdded: () => void;
  onCancel?: () => void;
  onError?: (error: string) => void;
  isEditing: boolean;
  showAddNode: boolean;
  isExecuting: boolean;
  executionResults: Record<number, { type: 'text' | 'pdf'; result: string }>;
}

export function WorkflowItem({ 
  workflow, 
  onEdit, 
  onSave, 
  onDelete, 
  onRun, 
  onAddNode,
  onShowResult,
  onNodeDeleted,
  onNodeAdded,
  onCancel,
  onError,
  isEditing,
  showAddNode,
  isExecuting,
  executionResults
}: WorkflowItemProps) {
  const [editingName, setEditingName] = useState(workflow.name);
  const [editingInputType, setEditingInputType] = useState(workflow.input_type);
  const [editingOutputType, setEditingOutputType] = useState(workflow.output_type);
  const [editingInputData, setEditingInputData] = useState(workflow.input_data || '');
  const [editingSelectedFile, setEditingSelectedFile] = useState<File | null>(null);
  const [isSaving, setIsSaving] = useState(false);

  // 編集状態が開始されたときのみ状態を初期化
  useEffect(() => {
    if (isEditing) {
      setEditingName(workflow.name);
      setEditingInputType(workflow.input_type);
      setEditingOutputType(workflow.output_type);
      setEditingInputData(workflow.input_data || '');
      setEditingSelectedFile(null);
    }
  }, [isEditing, workflow.id]);

  const hasExecutionResult = !!executionResults[workflow.id];

  const inputTypeOptions = [
    { value: 'text', label: 'テキスト' },
    { value: 'pdf', label: 'PDF' }
  ];

  const outputTypeOptions = [
    { value: 'text', label: 'テキスト' },
    { value: 'pdf', label: 'PDF' }
  ];

  const handleSave = async () => {
    console.log('handleSave called');
    console.log('onSave function:', onSave);
    
    if (!editingName.trim()) {
      if (onError) {
        onError('ワークフロー名を入力してください');
      }
      return;
    }

    console.log('保存処理を開始:', {
      workflowId: workflow.id,
      name: editingName,
      input_type: editingInputType,
      output_type: editingOutputType,
      input_data: editingInputData,
      input_data_length: editingInputData?.length
    });

    setIsSaving(true);
    try {
      console.log('Calling onSave function...');
      // 編集保存のロジックは親コンポーネントで実装
      await onSave({
        ...workflow,
        name: editingName,
        input_type: editingInputType,
        output_type: editingOutputType,
        input_data: editingInputData
      }, editingSelectedFile);
      
      console.log('保存処理が完了しました');
    } catch (error) {
      console.error('保存処理でエラーが発生:', error);
      if (onError) {
        onError(error instanceof Error ? error.message : '保存に失敗しました');
      }
    } finally {
      setIsSaving(false);
    }
  };

  const handleCancel = () => {
    setEditingName(workflow.name);
    setEditingInputType(workflow.input_type);
    setEditingOutputType(workflow.output_type);
    setEditingInputData(workflow.input_data || '');
    setEditingSelectedFile(null);
    // 編集状態を終了
    if (onCancel) {
      onCancel();
    }
  };

  return (
    <div className="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
      <div className="p-4">
        {isEditing ? (
          <div className="space-y-4">
            <InputField
              label="ワークフロー名"
              value={editingName}
              onChange={setEditingName}
            />
            
            <div className="grid grid-cols-1 gap-4">
              <SelectField
                label="入力種別"
                value={editingInputType}
                onChange={(value) => setEditingInputType(value as 'text' | 'pdf')}
                options={inputTypeOptions}
              />
              
              <SelectField
                label="出力種別"
                value={editingOutputType}
                onChange={(value) => setEditingOutputType(value as 'text' | 'pdf')}
                options={outputTypeOptions}
              />
            </div>

            {editingInputType === 'text' ? (
              <TextareaField
                label="入力データ"
                value={editingInputData}
                onChange={(value) => {
                  console.log('入力データが変更されました:', value);
                  setEditingInputData(value);
                }}
                rows={3}
              />
            ) : (
              <FileUpload
                label="PDFファイル"
                accept=".pdf"
                onChange={setEditingSelectedFile}
              />
            )}

            <div className="flex gap-2">
              <Button
                variant="success"
                size="sm"
                onClick={handleSave}
                disabled={isSaving}
              >
                {isSaving ? '保存中...' : '保存'}
              </Button>
              <Button
                variant="secondary"
                size="sm"
                onClick={handleCancel}
                disabled={isSaving}
              >
                キャンセル
              </Button>
            </div>
          </div>
        ) : (
          <>
            <div className="mb-3">
              <h3 className="text-lg font-medium text-gray-900 mb-2">{workflow.name}</h3>
              <div className="flex flex-wrap gap-2 text-xs text-gray-500">
                <span className="px-2 py-1 bg-blue-100 text-blue-800 rounded">
                  入力: {workflow.input_type === 'text' ? 'テキスト' : 'PDF'}
                </span>
                <span className="px-2 py-1 bg-green-100 text-green-800 rounded">
                  出力: {workflow.output_type === 'text' ? 'テキスト' : 'PDF'}
                </span>
                <span className="px-2 py-1 bg-gray-100 text-gray-800 rounded">
                  ノード: {workflow.nodes.length}
                </span>
              </div>
            </div>
            
            <div className="flex flex-wrap gap-2">
              <Button
                variant="primary"
                size="sm"
                onClick={() => onEdit(workflow, null)}
                disabled={isExecuting}
              >
                編集
              </Button>
              <Button
                variant="success"
                size="sm"
                onClick={() => onRun(workflow.id)}
                disabled={isExecuting}
              >
                {isExecuting ? '実行中...' : '実行'}
              </Button>
              <Button
                variant="primary"
                size="sm"
                onClick={() => onShowResult(workflow.id)}
                disabled={!hasExecutionResult || isExecuting}
              >
                結果表示
              </Button>
              <Button
                variant="warning"
                size="sm"
                onClick={() => onAddNode(workflow.id)}
                disabled={isExecuting}
              >
                ノード追加
              </Button>
              <Button
                variant="danger"
                size="sm"
                onClick={() => onDelete(workflow.id)}
                disabled={isExecuting}
              >
                削除
              </Button>
            </div>
          </>
        )}
      </div>
      
      {showAddNode && (
        <div className="px-4 pb-4 border-t border-gray-100">
          <NodeForm 
            workflowId={workflow.id} 
            workflow={workflow}
            onSuccess={() => {
              onAddNode(workflow.id);
              onNodeAdded();
            }}
            onCancel={() => onAddNode(workflow.id)}
            onError={onError}
          />
        </div>
      )}
      
      {!isEditing && !showAddNode && workflow.nodes.length > 0 && (
        <div className="px-4 pb-4 border-t border-gray-100">
          <div className="mt-3">
            <h4 className="text-sm font-medium text-gray-700 mb-2">ノード一覧</h4>
            <div className="max-h-32 overflow-y-auto">
              <NodeList
                workflowId={workflow.id}
                nodes={workflow.nodes}
                onNodeDeleted={onNodeDeleted}
                onError={onError}
              />
            </div>
          </div>
        </div>
      )}
    </div>
  );
} 