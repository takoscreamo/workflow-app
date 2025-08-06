export interface Workflow {
  id: number;
  name: string;
  created_at: string;
  updated_at: string;
  nodes?: Node[];
}

export interface Node {
  id: number;
  workflow_id: number;
  node_type: string;
  config: Record<string, unknown>;
  created_at: string;
  updated_at: string;
}

export enum NodeType {
  EXTRACT_TEXT = 'extract_text',
  GENERATIVE_AI = 'generative_ai',
  FORMATTER = 'formatter',
}

export interface CreateWorkflowRequest {
  name: string;
}

export interface AddNodeRequest {
  node_type: string;
  config: Record<string, unknown>;
}

export interface WorkflowExecutionResult {
  workflow_id: number;
  workflow_name: string;
  results: Array<{
    node_id: number;
    node_type: string;
    config: Record<string, unknown>;
    result: string;
    status: 'success' | 'error';
    error?: string;
  }>;
  final_result: string | null;
} 