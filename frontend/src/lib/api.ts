import { Workflow, CreateWorkflowRequest, AddNodeRequest, WorkflowExecutionResult, Node } from '@/types/workflow';

const API_BASE_URL = 'http://localhost:8000/api';

export const api = {
  // ワークフロー一覧を取得
  async getWorkflows(): Promise<Workflow[]> {
    const response = await fetch(`${API_BASE_URL}/workflows`);
    if (!response.ok) {
      throw new Error('ワークフロー一覧の取得に失敗しました');
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

  // ワークフローを取得
  async getWorkflow(id: number): Promise<Workflow> {
    const response = await fetch(`${API_BASE_URL}/workflows/${id}`);
    if (!response.ok) {
      throw new Error('ワークフローの取得に失敗しました');
    }
    return response.json();
  },

  // ワークフローを更新
  async updateWorkflow(id: number, data: CreateWorkflowRequest): Promise<Workflow> {
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

  // ワークフローにノードを追加
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
  async runWorkflow(id: number): Promise<WorkflowExecutionResult> {
    const response = await fetch(`${API_BASE_URL}/workflows/${id}/run`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
    });
    if (!response.ok) {
      throw new Error('ワークフローの実行に失敗しました');
    }
    return response.json();
  },
}; 