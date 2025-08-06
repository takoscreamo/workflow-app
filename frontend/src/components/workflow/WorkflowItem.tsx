import { useState } from 'react';
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
  onDelete: (id: number) => void;
  onRun: (id: number) => void;
  onAddNode: (id: number) => void;
  onShowResult: (id: number) => void;
  onNodeDeleted: () => void;
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
  onDelete, 
  onRun, 
  onAddNode,
  onShowResult,
  onNodeDeleted,
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

  const hasExecutionResult = !!executionResults[workflow.id];

  const inputTypeOptions = [
    { value: 'text', label: 'テキスト' },
    { value: 'pdf', label: 'PDF' }
  ];

  const outputTypeOptions = [
    { value: 'text', label: 'テキスト' },
    { value: 'pdf', label: 'PDF' }
  ];

  const handleSave = () => {
    // 編集保存のロジックは親コンポーネントで実装
    onEdit({
      ...workflow,
      name: editingName,
      input_type: editingInputType,
      output_type: editingOutputType,
      input_data: editingInputData
    }, editingSelectedFile);
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
    <div className="px-6 py-4">
      <div className="flex items-center justify-between">
        <div className="flex-1">
          {isEditing ? (
            <div className="space-y-4">
              <InputField
                label="ワークフロー名"
                value={editingName}
                onChange={setEditingName}
              />
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                  onChange={setEditingInputData}
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
                >
                  保存
                </Button>
                <Button
                  variant="secondary"
                  size="sm"
                  onClick={handleCancel}
                >
                  キャンセル
                </Button>
              </div>
            </div>
          ) : (
            <>
              <div className="flex items-center gap-4">
                <h3 className="text-lg font-medium">{workflow.name}</h3>
                <div className="flex gap-2 text-sm text-gray-500">
                  <span>入力: {workflow.input_type === 'text' ? 'テキスト' : 'PDF'}</span>
                  <span>出力: {workflow.output_type === 'text' ? 'テキスト' : 'PDF'}</span>
                </div>
              </div>
              <div className="mt-2 text-sm text-gray-600">
                ノード数: {workflow.nodes.length}
              </div>
            </>
          )}
        </div>
        
        {!isEditing && (
          <div className="flex gap-2">
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
        )}
      </div>
      
      {showAddNode && (
        <div className="mt-4">
          <NodeForm 
            workflowId={workflow.id} 
            onSuccess={() => onAddNode(workflow.id)}
            onCancel={() => onAddNode(workflow.id)}
            onError={onError}
          />
        </div>
      )}
      
      {!isEditing && !showAddNode && (
        <NodeList
          workflowId={workflow.id}
          nodes={workflow.nodes}
          onNodeDeleted={onNodeDeleted}
          onError={onError}
        />
      )}
    </div>
  );
} 