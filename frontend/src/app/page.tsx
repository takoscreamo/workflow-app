'use client';

import { useState, useEffect } from 'react';
import { Workflow, NodeType } from '@/types/workflow';
import { api } from '@/lib/api';

export default function Home() {
  const [workflows, setWorkflows] = useState<Workflow[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [newWorkflowName, setNewWorkflowName] = useState('');
  const [newWorkflowInputType, setNewWorkflowInputType] = useState<'text' | 'pdf'>('text');
  const [newWorkflowOutputType, setNewWorkflowOutputType] = useState<'text' | 'pdf'>('text');
  const [newWorkflowInputData, setNewWorkflowInputData] = useState('');
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [editingWorkflow, setEditingWorkflow] = useState<number | null>(null);
  const [editingName, setEditingName] = useState('');
  const [editingInputType, setEditingInputType] = useState<'text' | 'pdf'>('text');
  const [editingOutputType, setEditingOutputType] = useState<'text' | 'pdf'>('text');
  const [editingInputData, setEditingInputData] = useState('');
  const [editingSelectedFile, setEditingSelectedFile] = useState<File | null>(null);
  const [showAddNode, setShowAddNode] = useState<number | null>(null);
  const [nodeType, setNodeType] = useState<NodeType>('' as NodeType);
  const [nodeConfig, setNodeConfig] = useState<Record<string, unknown>>({});
  const [executionResult, setExecutionResult] = useState<string | null>(null);
  const [showExecutionResult, setShowExecutionResult] = useState(false);

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

  const handleFileUpload = async (file: File): Promise<string> => {
    try {
      const result = await api.uploadFile(file);
      return result.file_path;
    } catch (err) {
      throw new Error('ファイルのアップロードに失敗しました');
    }
  };

  const createWorkflow = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newWorkflowName.trim()) return;

    try {
      let inputData = newWorkflowInputData;
      
      // PDFファイルが選択されている場合はアップロード
      if (newWorkflowInputType === 'pdf' && selectedFile) {
        inputData = await handleFileUpload(selectedFile);
      }

      const workflow = await api.createWorkflow({ 
        name: newWorkflowName,
        input_type: newWorkflowInputType,
        output_type: newWorkflowOutputType,
        input_data: inputData
      });

      // ワークフロー一覧を再読み込みして最新のデータを取得
      await loadWorkflows();
      setNewWorkflowName('');
      setNewWorkflowInputType('text');
      setNewWorkflowOutputType('text');
      setNewWorkflowInputData('');
      setSelectedFile(null);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'ワークフローの作成に失敗しました');
    }
  };

  const startEditing = (workflow: Workflow) => {
    setEditingWorkflow(workflow.id);
    setEditingName(workflow.name);
    setEditingInputType(workflow.input_type);
    setEditingOutputType(workflow.output_type);
    setEditingInputData(workflow.input_data || '');
    setEditingSelectedFile(null);
  };

  const cancelEditing = () => {
    setEditingWorkflow(null);
    setEditingName('');
    setEditingInputType('text');
    setEditingOutputType('text');
    setEditingInputData('');
    setEditingSelectedFile(null);
  };

  const updateWorkflow = async (id: number) => {
    if (!editingName.trim()) return;

    try {
      let inputData = editingInputData;
      
      // PDFファイルが選択されている場合はアップロード
      if (editingInputType === 'pdf' && editingSelectedFile) {
        inputData = await handleFileUpload(editingSelectedFile);
      }

      const updatedWorkflow = await api.updateWorkflow(id, { 
        name: editingName,
        input_type: editingInputType,
        output_type: editingOutputType,
        input_data: inputData
      });
      
      // ワークフロー一覧を再読み込みして最新のデータを取得
      await loadWorkflows();
      setEditingWorkflow(null);
      setEditingName('');
      setEditingInputType('text');
      setEditingOutputType('text');
      setEditingInputData('');
      setEditingSelectedFile(null);
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
      
      if (result.output_type === 'pdf' && result.final_result) {
        // PDFとしてダウンロード
        await api.downloadPdf(result.final_result, `workflow_result_${id}.pdf`);
      } else if (result.final_result) {
        // テキストとして表示
        setExecutionResult(result.final_result);
        setShowExecutionResult(true);
      } else {
        setExecutionResult('結果がありません');
        setShowExecutionResult(true);
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'ワークフローの実行に失敗しました');
    }
  };

  const addNode = async (workflowId: number) => {
    try {
      if (!nodeType) {
        setError('ノードタイプを選択してください');
        return;
      }
      
      const requestData = { node_type: nodeType, config: nodeConfig };
      console.log('送信データ:', requestData);
      console.log('ノードタイプ:', nodeType);
      console.log('ノード設定:', nodeConfig);
      
      await api.addNode(workflowId, requestData);
      await loadWorkflows(); // ワークフロー一覧を再読み込み
      setShowAddNode(null);
      setNodeConfig({});
      setNodeType('' as NodeType);
    } catch (err) {
      console.error('ノード追加エラー:', err);
      setError(err instanceof Error ? err.message : 'ノードの追加に失敗しました');
    }
  };

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
    console.log('ノードタイプ変更:', type);
    setNodeType(type);
    const defaultConfig = getDefaultConfig(type);
    console.log('デフォルト設定:', defaultConfig);
    setNodeConfig(defaultConfig);
  };

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    alert('クリップボードにコピーしました');
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
          <form onSubmit={createWorkflow} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">ワークフロー名</label>
              <input
                type="text"
                value={newWorkflowName}
                onChange={(e) => setNewWorkflowName(e.target.value)}
                placeholder="ワークフロー名を入力"
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">入力種別</label>
                <select
                  value={newWorkflowInputType}
                  onChange={(e) => setNewWorkflowInputType(e.target.value as 'text' | 'pdf')}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="text">テキスト</option>
                  <option value="pdf">PDF</option>
                </select>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">出力種別</label>
                <select
                  value={newWorkflowOutputType}
                  onChange={(e) => setNewWorkflowOutputType(e.target.value as 'text' | 'pdf')}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="text">テキスト</option>
                  <option value="pdf">PDF</option>
                </select>
              </div>
            </div>

            {newWorkflowInputType === 'text' ? (
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">入力データ</label>
                <textarea
                  value={newWorkflowInputData}
                  onChange={(e) => setNewWorkflowInputData(e.target.value)}
                  placeholder="入力テキストを入力してください"
                  rows={4}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            ) : (
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">PDFファイル</label>
                <input
                  type="file"
                  accept=".pdf"
                  onChange={(e) => setSelectedFile(e.target.files?.[0] || null)}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            )}

            <button
              type="submit"
              className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              作成
            </button>
          </form>
        </div>

        {/* 実行結果表示モーダル */}
        {showExecutionResult && executionResult && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-semibold">実行結果</h3>
                <button
                  onClick={() => setShowExecutionResult(false)}
                  className="text-gray-500 hover:text-gray-700"
                >
                  ✕
                </button>
              </div>
              <div className="bg-gray-100 p-4 rounded-lg mb-4">
                <pre className="whitespace-pre-wrap text-sm">{executionResult}</pre>
              </div>
              <div className="flex justify-end gap-2">
                <button
                  onClick={() => copyToClipboard(executionResult)}
                  className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                >
                  コピー
                </button>
                <button
                  onClick={() => setShowExecutionResult(false)}
                  className="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
                >
                  閉じる
                </button>
              </div>
            </div>
          </div>
        )}

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
                        <div className="space-y-4">
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">ワークフロー名</label>
                            <input
                              type="text"
                              value={editingName}
                              onChange={(e) => setEditingName(e.target.value)}
                              className="w-full px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                          </div>
                          
                          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                              <label className="block text-sm font-medium text-gray-700 mb-1">入力種別</label>
                              <select
                                value={editingInputType}
                                onChange={(e) => setEditingInputType(e.target.value as 'text' | 'pdf')}
                                className="w-full px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                              >
                                <option value="text">テキスト</option>
                                <option value="pdf">PDF</option>
                              </select>
                            </div>
                            
                            <div>
                              <label className="block text-sm font-medium text-gray-700 mb-1">出力種別</label>
                              <select
                                value={editingOutputType}
                                onChange={(e) => setEditingOutputType(e.target.value as 'text' | 'pdf')}
                                className="w-full px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                              >
                                <option value="text">テキスト</option>
                                <option value="pdf">PDF</option>
                              </select>
                            </div>
                          </div>

                          {editingInputType === 'text' ? (
                            <div>
                              <label className="block text-sm font-medium text-gray-700 mb-1">入力データ</label>
                              <textarea
                                value={editingInputData}
                                onChange={(e) => setEditingInputData(e.target.value)}
                                rows={3}
                                className="w-full px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                              />
                            </div>
                          ) : (
                            <div>
                              <label className="block text-sm font-medium text-gray-700 mb-1">PDFファイル</label>
                              <input
                                type="file"
                                accept=".pdf"
                                onChange={(e) => setEditingSelectedFile(e.target.files?.[0] || null)}
                                className="w-full px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                              />
                            </div>
                          )}

                          <div className="flex gap-2">
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
                    
                    {editingWorkflow !== workflow.id && (
                      <div className="flex gap-2">
                        <button
                          onClick={() => startEditing(workflow)}
                          className="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                          編集
                        </button>
                        <button
                          onClick={() => runWorkflow(workflow.id)}
                          className="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                          実行
                        </button>
                        <button
                          onClick={() => setShowAddNode(showAddNode === workflow.id ? null : workflow.id)}
                          className="px-3 py-1 bg-purple-600 text-white rounded hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        >
                          ノード追加
                        </button>
                        <button
                          onClick={() => deleteWorkflow(workflow.id)}
                          className="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                          削除
                        </button>
                      </div>
                    )}
                  </div>
                  
                  {/* ノード追加フォーム */}
                  {showAddNode === workflow.id && (
                    <div className="mt-4 p-4 bg-gray-50 rounded-lg">
                      <h4 className="text-sm font-medium text-gray-700 mb-3">ノードを追加</h4>
                      <div className="space-y-3">
                        <div>
                          <label className="block text-xs text-gray-600 mb-1">ノードタイプ</label>
                          <select
                            value={nodeType || ''}
                            onChange={(e) => {
                              const selectedType = e.target.value as NodeType;
                              console.log('ノードタイプ選択:', selectedType);
                              handleNodeTypeChange(selectedType);
                            }}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          >
                            <option value="">ノードタイプを選択</option>
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
                          <div className="bg-blue-50 p-3 rounded-lg">
                            <p className="text-sm text-blue-700">
                              PDFファイルは入力時にアップロードしてください。このノードは入力のPDFからテキストを自動抽出します。
                            </p>
                          </div>
                        )}

                        {nodeType === NodeType.GENERATIVE_AI && (
                          <>
                            <div>
                              <label className="block text-xs text-gray-600 mb-1">プロンプト</label>
                              <textarea
                                value={nodeConfig.prompt as string || ''}
                                onChange={(e) => setNodeConfig({ ...nodeConfig, prompt: e.target.value })}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                rows={3}
                                placeholder="AIへの指示を入力してください"
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
                          </>
                        )}

                        <div className="flex gap-2">
                          <button
                            onClick={() => addNode(workflow.id)}
                            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          >
                            追加
                          </button>
                          <button
                            onClick={() => setShowAddNode(null)}
                            className="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                          >
                            キャンセル
                          </button>
                        </div>
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
