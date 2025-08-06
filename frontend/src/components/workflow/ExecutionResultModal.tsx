import { Modal } from '@/components/common/Modal';
import { Button } from '@/components/common/Button';

interface ExecutionResultModalProps {
  isOpen: boolean;
  onClose: () => void;
  result: string | null;
}

export function ExecutionResultModal({ isOpen, onClose, result }: ExecutionResultModalProps) {
  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    alert('クリップボードにコピーしました');
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="実行結果">
      <div className="bg-gray-100 p-4 rounded-lg">
        <pre className="whitespace-pre-wrap text-sm leading-relaxed">{result}</pre>
      </div>
      <div className="flex justify-end gap-2 p-6 border-t border-gray-200">
        <Button
          variant="success"
          onClick={() => result && copyToClipboard(result)}
        >
          コピー
        </Button>
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