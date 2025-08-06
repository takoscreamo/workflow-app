import { Modal } from '@/components/common/Modal';
import { Button } from '@/components/common/Button';
import { api } from '@/lib/api';

interface ExecutionResultModalProps {
  isOpen: boolean;
  onClose: () => void;
  result: string | null;
  resultType?: 'text' | 'pdf';
  workflowId?: number;
}

export function ExecutionResultModal({ 
  isOpen, 
  onClose, 
  result, 
  resultType = 'text',
  workflowId 
}: ExecutionResultModalProps) {
  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    alert('クリップボードにコピーしました');
  };

  const downloadPdf = async () => {
    if (result && resultType === 'pdf') {
      try {
        await api.downloadPdf(result, `workflow_result_${workflowId || 'download'}.pdf`);
      } catch (error) {
        console.error('PDF download error:', error);
        alert('PDFのダウンロードに失敗しました');
      }
    }
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="実行結果">
      {resultType === 'pdf' ? (
        <div className="p-6">
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <div className="flex items-center">
              <svg className="w-8 h-8 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clipRule="evenodd" />
              </svg>
              <div>
                <h3 className="font-medium text-blue-900">PDFファイルが生成されました</h3>
                <p className="font-medium text-blue-900">下のボタンをクリックしてPDFをダウンロードしてください</p>
              </div>
            </div>
          </div>
        </div>
      ) : (
        <div className="bg-gray-100 p-4 rounded-lg">
          <pre className="whitespace-pre-wrap text-sm leading-relaxed">{result}</pre>
        </div>
      )}
      
      <div className="flex justify-end gap-2 p-6 border-t border-gray-200">
        {resultType === 'text' && result && (
          <Button
            variant="success"
            onClick={() => copyToClipboard(result)}
          >
            コピー
          </Button>
        )}
        {resultType === 'pdf' && (
          <Button
            variant="success"
            onClick={downloadPdf}
          >
            PDFダウンロード
          </Button>
        )}
        <Button
          variant="secondary"
          onClick={onClose}
        >
          閉じる
        </Button>
      </div>
    </Modal>
  );
} 