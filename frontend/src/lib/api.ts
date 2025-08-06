import { Workflow, CreateWorkflowRequest, UpdateWorkflowRequest, AddNodeRequest, WorkflowRunResult, Node } from '@/types/workflow';

const API_BASE_URL = 'http://localhost:8000/api';

// デバッグ用ログ
console.log('API_BASE_URL:', API_BASE_URL);

export const api = {
  // ワークフロー一覧を取得
  async getWorkflows(): Promise<Workflow[]> {
    const response = await fetch(`${API_BASE_URL}/workflows`);
    if (!response.ok) {
      throw new Error('ワークフローの取得に失敗しました');
    }
    return response.json();
  },

  // ワークフローを作成
  async createWorkflow(data: CreateWorkflowRequest): Promise<Workflow> {
    const response = await fetch(`${API_BASE_URL}/workflows`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    });
    if (!response.ok) {
      throw new Error('ワークフローの作成に失敗しました');
    }
    return response.json();
  },

  // ワークフローを更新
  async updateWorkflow(id: number, data: UpdateWorkflowRequest): Promise<Workflow> {
    const response = await fetch(`${API_BASE_URL}/workflows/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    });
    if (!response.ok) {
      throw new Error('ワークフローの更新に失敗しました');
    }
    return response.json();
  },

  // ワークフローを削除
  async deleteWorkflow(id: number): Promise<void> {
    const response = await fetch(`${API_BASE_URL}/workflows/${id}`, {
      method: 'DELETE',
    });
    if (!response.ok) {
      throw new Error('ワークフローの削除に失敗しました');
    }
  },

  // ノードを追加
  async addNode(workflowId: number, data: AddNodeRequest): Promise<Node> {
    const response = await fetch(`${API_BASE_URL}/workflows/${workflowId}/nodes`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    });
    if (!response.ok) {
      throw new Error('ノードの追加に失敗しました');
    }
    return response.json();
  },

  // ワークフローを実行
  async runWorkflow(id: number): Promise<WorkflowRunResult> {
    const response = await fetch(`${API_BASE_URL}/workflows/${id}/run`, {
      method: 'POST',
    });
    if (!response.ok) {
      throw new Error('ワークフローの実行に失敗しました');
    }
    return response.json();
  },

  // ファイルをアップロード
  async uploadFile(file: File): Promise<{ file_path: string }> {
    const formData = new FormData();
    formData.append('file', file);

    const response = await fetch(`${API_BASE_URL}/files/upload`, {
      method: 'POST',
      body: formData,
    });
    if (!response.ok) {
      throw new Error('ファイルのアップロードに失敗しました');
    }
    return response.json();
  },

  // PDFファイルをダウンロード
  async downloadPdf(content: string, filename: string): Promise<void> {
    // Base64デコード
    const binaryString = atob(content);
    const bytes = new Uint8Array(binaryString.length);
    for (let i = 0; i < binaryString.length; i++) {
      bytes[i] = binaryString.charCodeAt(i);
    }
    
    const blob = new Blob([bytes], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  },
}; 