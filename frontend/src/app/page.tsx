'use client';

import { useState, useEffect } from 'react';
import { Workflow, NodeType } from '@/types/workflow';
import { api } from '@/lib/api';

export default function Home() {
  const [workflows, setWorkflows] = useState<Workflow[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [newWorkflowName, setNewWorkflowName] = useState('');
  const [editingWorkflow, setEditingWorkflow] = useState<number | null>(null);
  const [editingName, setEditingName] = useState('');
  const [showAddNode, setShowAddNode] = useState<number | null>(null);
  const [nodeType, setNodeType] = useState<NodeType>(NodeType.FORMATTER);
  const [nodeConfig, setNodeConfig] = useState<Record<string, unknown>>({});

  useEffect(() => {
    loadWorkflows();
  }, []);

  const loadWorkflows = async () => {
    try {
      setLoading(true);
      const data = await api.getWorkflows();
      setWorkflows(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'エラーが発生しました');
    } finally {
      setLoading(false);
    }
  };

  const createWorkflow = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newWorkflowName.trim()) return;

    try {
      const workflow = await api.createWorkflow({ name: newWorkflowName });
      setWorkflows([...workflows, workflow]);
      setNewWorkflowName('');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'ワークフローの作成に失敗しました');
    }
  };

  const startEditing = (workflow: Workflow) => {
    setEditingWorkflow(workflow.id);
    setEditingName(workflow.name);
  };

  const cancelEditing = () => {
    setEditingWorkflow(null);
    setEditingName('');
  };

  const updateWorkflow = async (id: number) => {
    if (!editingName.trim()) return;

    try {
      const updatedWorkflow = await api.updateWorkflow(id, { name: editingName });
      setWorkflows(workflows.map(w => w.id === id ? updatedWorkflow : w));
      setEditingWorkflow(null);
      setEditingName('');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'ワークフローの更新に失敗しました');
    }
  };

  const deleteWorkflow = async (id: number) => {
    if (!confirm('このワークフローを削除しますか？')) return;

    try {
      await api.deleteWorkflow(id);
      setWorkflows(workflows.filter(w => w.id !== id));
    } catch (err) {
      setError(err instanceof Error ? err.message : 'ワークフローの削除に失敗しました');
    }
  };

  const runWorkflow = async (id: number) => {
    try {
      const result = await api.runWorkflow(id);
      console.log('実行結果:', result);
      alert(`実行完了！最終結果: ${result.final_result || 'なし'}`);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'ワークフローの実行に失敗しました');
    }
  };

  const addNode = async (workflowId: number) => {
    try {
      await api.addNode(workflowId, { node_type: nodeType, config: nodeConfig });
      await loadWorkflows(); // ワークフロー一覧を再読み込み
      setShowAddNode(null);
      setNodeConfig({});
    } catch (err) {
      setError(err instanceof Error ? err.message : 'ノードの追加に失敗しました');
    }
  };

  const getDefaultConfig = (type: NodeType): Record<string, unknown> => {
    switch (type) {
      case NodeType.FORMATTER:
        return { format_type: 'uppercase', description: 'テキストを大文字に変換' };
      case NodeType.EXTRACT_TEXT:
        return { file_path: '', description: 'PDFからテキストを抽出' };
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
    setNodeConfig(getDefaultConfig(type));
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-xl">読み込み中...</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">
          ワークフローアプリケーション
        </h1>

        {error && (
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {error}
          </div>
        )}

        {/* 新しいワークフロー作成フォーム */}
        <div className="bg-white rounded-lg shadow p-6 mb-8">
          <h2 className="text-xl font-semibold mb-4">新しいワークフローを作成</h2>
          <form onSubmit={createWorkflow} className="flex gap-4">
            <input
              type="text"
              value={newWorkflowName}
              onChange={(e) => setNewWorkflowName(e.target.value)}
              placeholder="ワークフロー名を入力"
              className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button
              type="submit"
              className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              作成
            </button>
          </form>
        </div>

        {/* ワークフロー一覧 */}
        <div className="bg-white rounded-lg shadow">
          <div className="px-6 py-4 border-b border-gray-200">
            <h2 className="text-xl font-semibold">ワークフロー一覧</h2>
          </div>
          <div className="divide-y divide-gray-200">
            {workflows.length === 0 ? (
              <div className="px-6 py-8 text-center text-gray-500">
                ワークフローがありません
              </div>
            ) : (
              workflows.map((workflow) => (
                <div key={workflow.id} className="px-6 py-4">
                  <div className="flex items-center justify-between">
                    <div className="flex-1">
                      {editingWorkflow === workflow.id ? (
                        <div className="flex items-center gap-2">
                          <input
                            type="text"
                            value={editingName}
                            onChange={(e) => setEditingName(e.target.value)}
                            className="flex-1 px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                          />
                          <button
                            onClick={() => updateWorkflow(workflow.id)}
                            className="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                          >
                            保存
                          </button>
                          <button
                            onClick={cancelEditing}
                            className="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                          >
                            キャンセル
                          </button>
                        </div>
                      ) : (
                        <div>
                          <h3 className="text-lg font-medium text-gray-900">
                            {workflow.name}
                          </h3>
                          <p className="text-sm text-gray-500">
                            ノード数: {workflow.nodes?.length || 0}
                          </p>
                        </div>
                      )}
                    </div>
                    <div className="flex gap-2">
                      {editingWorkflow !== workflow.id && (
                        <>
                          <button
                            onClick={() => setShowAddNode(workflow.id)}
                            className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
                          >
                            ノード追加
                          </button>
                          <button
                            onClick={() => startEditing(workflow)}
                            className="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500"
                          >
                            編集
                          </button>
                          <button
                            onClick={() => runWorkflow(workflow.id)}
                            className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                          >
                            実行
                          </button>
                          <button
                            onClick={() => deleteWorkflow(workflow.id)}
                            className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                          >
                            削除
                          </button>
                        </>
                      )}
                    </div>
                  </div>
                  
                  {/* ノード追加フォーム */}
                  {showAddNode === workflow.id && (
                    <div className="mt-4 p-4 bg-gray-50 rounded-lg">
                      <h4 className="text-sm font-medium text-gray-700 mb-3">ノードを追加</h4>
                      <div className="space-y-3">
                        <div>
                          <label className="block text-xs text-gray-600 mb-1">ノードタイプ</label>
                          <select
                            value={nodeType}
                            onChange={(e) => handleNodeTypeChange(e.target.value as NodeType)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          >
                            <option value={NodeType.FORMATTER}>FORMATTER - テキスト整形</option>
                            <option value={NodeType.EXTRACT_TEXT}>EXTRACT_TEXT - PDFテキスト抽出</option>
                            <option value={NodeType.GENERATIVE_AI}>GENERATIVE_AI - AI処理</option>
                          </select>
                        </div>
                        
                        {nodeType === NodeType.FORMATTER && (
                          <div>
                            <label className="block text-xs text-gray-600 mb-1">フォーマットタイプ</label>
                            <select
                              value={nodeConfig.format_type as string || 'uppercase'}
                              onChange={(e) => setNodeConfig({ ...nodeConfig, format_type: e.target.value })}
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                              <option value="uppercase">大文字に変換</option>
                              <option value="lowercase">小文字に変換</option>
                              <option value="fullwidth">全角に変換</option>
                              <option value="halfwidth">半角に変換</option>
                            </select>
                          </div>
                        )}

                        {nodeType === NodeType.EXTRACT_TEXT && (
                          <div>
                            <label className="block text-xs text-gray-600 mb-1">ファイルパス</label>
                            <input
                              type="text"
                              value={nodeConfig.file_path as string || ''}
                              onChange={(e) => setNodeConfig({ ...nodeConfig, file_path: e.target.value })}
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="uploads/filename.pdf"
                            />
                          </div>
                        )}

                        {nodeType === NodeType.GENERATIVE_AI && (
                          <div className="space-y-3">
                            <div>
                              <label className="block text-xs text-gray-600 mb-1">プロンプト</label>
                              <textarea
                                value={nodeConfig.prompt as string || ''}
                                onChange={(e) => setNodeConfig({ ...nodeConfig, prompt: e.target.value })}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                rows={3}
                                placeholder="AIへの指示"
                              />
                            </div>
                            <div>
                              <label className="block text-xs text-gray-600 mb-1">モデル</label>
                              <select
                                value={nodeConfig.model as string || 'gpt-3.5-turbo'}
                                onChange={(e) => setNodeConfig({ ...nodeConfig, model: e.target.value })}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              >
                                <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                                <option value="gpt-4">GPT-4</option>
                              </select>
                            </div>
                          </div>
                        )}

                        <div className="flex gap-2">
                          <button
                            onClick={() => addNode(workflow.id)}
                            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          >
                            追加
                          </button>
                          <button
                            onClick={() => setShowAddNode(null)}
                            className="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                          >
                            キャンセル
                          </button>
                        </div>
                      </div>
                    </div>
                  )}

                  {workflow.nodes && workflow.nodes.length > 0 && (
                    <div className="mt-3">
                      <h4 className="text-sm font-medium text-gray-700 mb-2">ノード一覧:</h4>
                      <div className="flex flex-wrap gap-2">
                        {workflow.nodes.map((node) => (
                          <span
                            key={node.id}
                            className="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded"
                          >
                            {node.node_type}
                          </span>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              ))
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
