import { Node } from '@/types/workflow';
import { Button } from '@/components/common/Button';
import { api } from '@/lib/api';

interface NodeListProps {
  workflowId: number;
  nodes: Node[];
  onNodeDeleted: () => void;
  onError?: (error: string) => void;
}

export function NodeList({ workflowId, nodes, onNodeDeleted, onError }: NodeListProps) {
  const handleDeleteNode = async (nodeId: number) => {
    if (!confirm('このノードを削除しますか？')) {
      return;
    }

    try {
      await api.deleteNode(workflowId, nodeId);
      onNodeDeleted();
    } catch (error) {
      console.error('ノード削除エラー:', error);
      const errorMessage = error instanceof Error ? error.message : 'ノードの削除に失敗しました';
      onError?.(errorMessage);
    }
  };

  const getNodeTypeLabel = (nodeType: string): string => {
    switch (nodeType) {
      case 'formatter':
        return 'テキスト整形';
      case 'extract_text':
        return 'PDFテキスト抽出';
      case 'generative_ai':
        return 'AI処理';
      default:
        return nodeType;
    }
  };

  const getNodeDescription = (node: Node): string => {
    const config = node.config as Record<string, unknown>;
    
    switch (node.node_type) {
      case 'formatter':
        const formatType = config.format_type as string;
        switch (formatType) {
          case 'uppercase':
            return 'テキストを大文字に変換';
          case 'lowercase':
            return 'テキストを小文字に変換';
          case 'fullwidth':
            return 'テキストを全角に変換';
          case 'halfwidth':
            return 'テキストを半角に変換';
          default:
            return 'テキストを整形';
        }
      case 'extract_text':
        return 'PDFからテキストを自動抽出';
      case 'generative_ai':
        const prompt = config.prompt as string;
        return prompt ? `AI処理: ${prompt.substring(0, 50)}...` : 'AIでテキストを処理';
      default:
        return 'ノード処理';
    }
  };

  if (nodes.length === 0) {
    return (
      <div className="mt-4 p-4 bg-gray-50 rounded-lg">
        <p className="text-sm text-gray-500 text-center">ノードがありません</p>
      </div>
    );
  }

  return (
    <div className="mt-4 space-y-3">
      <h4 className="text-sm font-medium text-gray-700">ノード一覧</h4>
      {nodes.map((node, index) => (
        <div key={node.id} className="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg">
          <div className="flex items-center gap-3">
            <div className="flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-600 rounded-full text-sm font-medium">
              {index + 1}
            </div>
            <div>
              <div className="text-sm font-medium text-gray-900">
                {getNodeTypeLabel(node.node_type)}
              </div>
              <div className="text-xs text-gray-500">
                {getNodeDescription(node)}
              </div>
            </div>
          </div>
          <Button
            variant="danger"
            size="sm"
            onClick={() => handleDeleteNode(node.id)}
          >
            削除
          </Button>
        </div>
      ))}
    </div>
  );
} 