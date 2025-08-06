export interface Workflow {
  id: number;
  name: string;
  input_type: 'text' | 'pdf';
  output_type: 'text' | 'pdf';
  input_data?: string;
  created_at: string;
  updated_at: string;
  nodes: Node[];
}

export interface Node {
  id: number;
  workflow_id: number;
  node_type: NodeType;
  config: Record<string, unknown>;
  created_at: string;
  updated_at: string;
}

export enum NodeType {
  FORMATTER = 'formatter',
  EXTRACT_TEXT = 'extract_text',
  GENERATIVE_AI = 'generative_ai',
}

export interface CreateWorkflowRequest {
  name: string;
  input_type?: 'text' | 'pdf';
  output_type?: 'text' | 'pdf';
  input_data?: string;
}

export interface UpdateWorkflowRequest {
  name: string;
  input_type?: 'text' | 'pdf';
  output_type?: 'text' | 'pdf';
  input_data?: string;
}

export interface AddNodeRequest {
  node_type: NodeType;
  config: Record<string, unknown>;
}

export interface WorkflowRunResult {
  workflow_id: number;
  workflow_name: string;
  input_type: 'text' | 'pdf';
  output_type: 'text' | 'pdf';
  results: Array<{
    node_id: number;
    node_type: string;
    config: Record<string, unknown>;
    result: string | null;
    status: 'success' | 'error';
    error?: string;
  }>;
  final_result: string | null;
} 