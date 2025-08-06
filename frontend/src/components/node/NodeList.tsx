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
            return '大文字変換';
          case 'lowercase':
            return '小文字変換';
          case 'fullwidth':
            return '全角変換';
          case 'halfwidth':
            return '半角変換';
          default:
            return 'テキスト整形';
        }
      case 'extract_text':
        return 'PDFテキスト抽出';
      case 'generative_ai':
        const prompt = config.prompt as string;
        return prompt ? `${prompt.substring(0, 30)}...` : 'AI処理';
      default:
        return 'ノード処理';
    }
  };

  if (nodes.length === 0) {
    return (
      <div className="text-center py-2">
        <p className="text-xs text-gray-400">ノードがありません</p>
      </div>
    );
  }

  return (
    <div className="space-y-2">
      {nodes.map((node, index) => (
        <div key={node.id} className="flex items-center justify-between p-2 bg-gray-50 border border-gray-100 rounded text-xs">
          <div className="flex items-center gap-2">
            <div className="flex items-center justify-center w-5 h-5 bg-blue-100 text-blue-600 rounded-full text-xs font-medium">
              {index + 1}
            </div>
            <div>
              <div className="font-medium text-gray-700">
                {getNodeTypeLabel(node.node_type)}
              </div>
              <div className="text-gray-500">
                {getNodeDescription(node)}
              </div>
            </div>
          </div>
          <Button
            variant="danger"
            size="sm"
            onClick={() => handleDeleteNode(node.id)}
            className="text-xs px-2 py-1"
          >
            削除
          </Button>
        </div>
      ))}
    </div>
  );
} 