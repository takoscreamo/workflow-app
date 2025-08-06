import { useState } from 'react';
import { InputField } from '@/components/forms/InputField';
import { SelectField } from '@/components/forms/SelectField';
import { TextareaField } from '@/components/forms/TextareaField';
import { FileUpload } from '@/components/forms/FileUpload';
import { Button } from '@/components/common/Button';

interface WorkflowFormData {
  name: string;
  input_type: 'text' | 'pdf';
  output_type: 'text' | 'pdf';
  input_data: string;
}

interface WorkflowFormProps {
  onSubmit: (data: WorkflowFormData, file: File | null) => Promise<void>;
  onCancel?: () => void;
  initialData?: Partial<WorkflowFormData>;
  submitLabel?: string;
}

export function WorkflowForm({ 
  onSubmit, 
  onCancel, 
  initialData = {}, 
  submitLabel = "作成" 
}: WorkflowFormProps) {
  const [formData, setFormData] = useState<WorkflowFormData>({
    name: initialData.name || '',
    input_type: initialData.input_type || 'text',
    output_type: initialData.output_type || 'text',
    input_data: initialData.input_data || ''
  });
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isExpanded, setIsExpanded] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.name.trim()) return;

    setIsSubmitting(true);
    try {
      await onSubmit(formData, selectedFile);
      // フォームをリセット
      setFormData({
        name: '',
        input_type: 'text',
        output_type: 'text',
        input_data: ''
      });
      setSelectedFile(null);
      setIsExpanded(false);
    } catch (error) {
      // エラーは親コンポーネントで処理
    } finally {
      setIsSubmitting(false);
    }
  };

  const inputTypeOptions = [
    { value: 'text', label: 'テキスト' },
    { value: 'pdf', label: 'PDF' }
  ];

  const outputTypeOptions = [
    { value: 'text', label: 'テキスト' },
    { value: 'pdf', label: 'PDF' }
  ];

  return (
    <div className="bg-white rounded-lg shadow p-4 mb-6">
      <div className="flex items-center justify-between mb-4">
        <h2 className="text-lg font-semibold">新しいワークフローを作成</h2>
        <Button
          variant="primary"
          size="sm"
          onClick={() => setIsExpanded(!isExpanded)}
        >
          {isExpanded ? '折りたたむ' : '展開'}
        </Button>
      </div>
      
      {isExpanded && (
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <InputField
              label="ワークフロー名"
              value={formData.name}
              onChange={(value) => setFormData({ ...formData, name: value })}
              placeholder="ワークフロー名を入力"
              required
            />
            
            <SelectField
              label="入力種別"
              value={formData.input_type}
              onChange={(value) => setFormData({ ...formData, input_type: value as 'text' | 'pdf' })}
              options={inputTypeOptions}
            />
            
            <SelectField
              label="出力種別"
              value={formData.output_type}
              onChange={(value) => setFormData({ ...formData, output_type: value as 'text' | 'pdf' })}
              options={outputTypeOptions}
            />
          </div>

          {formData.input_type === 'text' ? (
            <TextareaField
              label="入力データ"
              value={formData.input_data}
              onChange={(value) => setFormData({ ...formData, input_data: value })}
              placeholder="入力テキストを入力してください"
              rows={3}
            />
          ) : (
            <FileUpload
              label="PDFファイル"
              accept=".pdf"
              onChange={setSelectedFile}
            />
          )}

          <div className="flex gap-2">
            <Button
              type="submit"
              disabled={isSubmitting}
              className="flex-1"
            >
              {isSubmitting ? '処理中...' : submitLabel}
            </Button>
            {onCancel && (
              <Button
                type="button"
                variant="secondary"
                onClick={onCancel}
                disabled={isSubmitting}
              >
                キャンセル
              </Button>
            )}
          </div>
        </form>
      )}
    </div>
  );
} 