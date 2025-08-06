import { useState } from 'react';
import { NodeType } from '@/types/workflow';
import { SelectField } from '@/components/forms/SelectField';
import { TextareaField } from '@/components/forms/TextareaField';
import { Button } from '@/components/common/Button';
import { api } from '@/lib/api';

interface NodeFormProps {
  workflowId: number;
  onSuccess?: () => void;
  onCancel?: () => void;
  onError?: (error: string) => void;
}

export function NodeForm({ workflowId, onSuccess, onCancel, onError }: NodeFormProps) {
  const [nodeType, setNodeType] = useState<NodeType>('' as NodeType);
  const [nodeConfig, setNodeConfig] = useState<Record<string, unknown>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const nodeTypeOptions = [
    { value: NodeType.FORMATTER, label: 'FORMATTER - テキスト整形' },
    { value: NodeType.EXTRACT_TEXT, label: 'EXTRACT_TEXT - PDFテキスト抽出' },
    { value: NodeType.GENERATIVE_AI, label: 'GENERATIVE_AI - AI処理' }
  ];

  const getDefaultConfig = (type: NodeType): Record<string, unknown> => {
    switch (type) {
      case NodeType.FORMATTER:
        return { format_type: 'uppercase', description: 'テキストを大文字に変換' };
      case NodeType.EXTRACT_TEXT:
        return { description: '入力のPDFからテキストを自動抽出' };
      case NodeType.GENERATIVE_AI:
        return { 
          prompt: '以下のテキストを要約してください：', 
          model: 'gpt-3.5-turbo',
          max_tokens: 1000,
          temperature: 0.7,
          description: 'AIでテキストを処理'
        };
      default:
        return {};
    }
  };

  const handleNodeTypeChange = (type: NodeType) => {
    setNodeType(type);
    const defaultConfig = getDefaultConfig(type);
    setNodeConfig(defaultConfig);
  };

  const handleSubmit = async () => {
    if (!nodeType) {
      onError?.('ノードタイプを選択してください');
      return;
    }

    setIsSubmitting(true);
    try {
      const requestData = { node_type: nodeType, config: nodeConfig };
      await api.addNode(workflowId, requestData);
      
      // フォームをリセット
      setNodeType('' as NodeType);
      setNodeConfig({});
      
      onSuccess?.();
    } catch (error) {
      console.error('ノード追加エラー:', error);
      const errorMessage = error instanceof Error ? error.message : 'ノードの追加に失敗しました';
      onError?.(errorMessage);
    } finally {
      setIsSubmitting(false);
    }
  };

  const formatTypeOptions = [
    { value: 'uppercase', label: '大文字に変換' },
    { value: 'lowercase', label: '小文字に変換' },
    { value: 'fullwidth', label: '全角に変換' },
    { value: 'halfwidth', label: '半角に変換' }
  ];

  const modelOptions = [
    { value: 'gpt-3.5-turbo', label: 'GPT-3.5 Turbo' },
    { value: 'gpt-4', label: 'GPT-4' }
  ];

  return (
    <div className="mt-4 p-4 bg-gray-50 rounded-lg">
      <h4 className="text-sm font-medium text-gray-700 mb-3">ノードを追加</h4>
      <div className="space-y-3">
        <SelectField
          label="ノードタイプ"
          value={nodeType || ''}
          onChange={(value) => handleNodeTypeChange(value as NodeType)}
          options={nodeTypeOptions}
          placeholder="ノードタイプを選択"
        />
        
        {nodeType === NodeType.FORMATTER && (
          <SelectField
            label="フォーマットタイプ"
            value={nodeConfig.format_type as string || 'uppercase'}
            onChange={(value) => setNodeConfig({ ...nodeConfig, format_type: value })}
            options={formatTypeOptions}
          />
        )}

        {nodeType === NodeType.EXTRACT_TEXT && (
          <div className="bg-blue-50 p-3 rounded-lg">
            <p className="text-sm text-blue-700">
              PDFファイルは入力時にアップロードしてください。このノードは入力のPDFからテキストを自動抽出します。
            </p>
          </div>
        )}

        {nodeType === NodeType.GENERATIVE_AI && (
          <>
            <TextareaField
              label="プロンプト"
              value={nodeConfig.prompt as string || ''}
              onChange={(value) => setNodeConfig({ ...nodeConfig, prompt: value })}
              placeholder="AIへの指示を入力してください"
              rows={3}
            />
            <SelectField
              label="モデル"
              value={nodeConfig.model as string || 'gpt-3.5-turbo'}
              onChange={(value) => setNodeConfig({ ...nodeConfig, model: value })}
              options={modelOptions}
            />
          </>
        )}

        <div className="flex gap-2">
          <Button
            onClick={handleSubmit}
            disabled={!nodeType || isSubmitting}
          >
            {isSubmitting ? '追加中...' : '追加'}
          </Button>
          {onCancel && (
            <Button
              variant="secondary"
              onClick={onCancel}
              disabled={isSubmitting}
            >
              キャンセル
            </Button>
          )}
        </div>
      </div>
    </div>
  );
} 