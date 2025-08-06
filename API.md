# Workflow App - API仕様書

## 概要

- このドキュメントでは、バックエンドAPIの詳細仕様を説明します。
- [openapi.yaml](backend/public/api/openapi.yaml) でも確認できます。
- アプリケーション起動後はSwaggerUIも、http://localhost:8000/swagger で確認できます。

## ベースURL

```
http://localhost:8000/api
```

## 共通レスポンス形式

### 成功レスポンス

```json
{
  "success": true,
  "data": {
    // レスポンスデータ
  },
  "message": "操作が成功しました"
}
```

### エラーレスポンス

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "バリデーションエラーが発生しました",
    "details": {
      "field_name": ["エラーメッセージ"]
    }
  }
}
```

## エンドポイント一覧

### 1. ワークフロー管理

#### 1.1 ワークフロー一覧取得

**GET** `/workflows`

ワークフローの一覧を取得します。

**レスポンス例:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "サンプルワークフロー",
      "input_type": "text",
      "output_type": "text",
      "input_data": "サンプルテキスト",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z",
      "nodes": [
        {
          "id": 1,
          "workflow_id": 1,
          "node_type": "formatter",
          "config": {
            "format_type": "uppercase"
          },
          "order": 1,
          "created_at": "2024-01-01T00:00:00.000000Z",
          "updated_at": "2024-01-01T00:00:00.000000Z"
        }
      ]
    }
  ],
  "message": "ワークフロー一覧を取得しました"
}
```

#### 1.2 ワークフロー作成

**POST** `/workflows`

新しいワークフローを作成します。

**リクエスト例:**
```json
{
  "name": "新しいワークフロー",
  "input_type": "text",
  "output_type": "text",
  "input_data": "入力テキスト"
}
```

**リクエストパラメータ:**
- `name` (string, required): ワークフロー名
- `input_type` (string, required): 入力種別（`text` または `pdf`）
- `output_type` (string, required): 出力種別（`text` または `pdf`）
- `input_data` (string, required): 入力データ（テキストまたはファイルパス）

**レスポンス例:**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "新しいワークフロー",
    "input_type": "text",
    "output_type": "text",
    "input_data": "入力テキスト",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "nodes": []
  },
  "message": "ワークフローを作成しました"
}
```

#### 1.3 ワークフロー取得

**GET** `/workflows/{id}`

指定されたIDのワークフローを取得します。

**パラメータ:**
- `id` (integer, required): ワークフローID

**レスポンス例:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "サンプルワークフロー",
    "input_type": "text",
    "output_type": "text",
    "input_data": "サンプルテキスト",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "nodes": [
      {
        "id": 1,
        "workflow_id": 1,
        "node_type": "formatter",
        "config": {
          "format_type": "uppercase"
        },
        "order": 1,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
      }
    ]
  },
  "message": "ワークフローを取得しました"
}
```

#### 1.4 ワークフロー更新

**PUT** `/workflows/{id}`

指定されたIDのワークフローを更新します。

**パラメータ:**
- `id` (integer, required): ワークフローID

**リクエスト例:**
```json
{
  "name": "更新されたワークフロー",
  "input_type": "pdf",
  "output_type": "pdf",
  "input_data": "/storage/uploads/document.pdf"
}
```

**レスポンス例:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "更新されたワークフロー",
    "input_type": "pdf",
    "output_type": "pdf",
    "input_data": "/storage/uploads/document.pdf",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "nodes": []
  },
  "message": "ワークフローを更新しました"
}
```

#### 1.5 ワークフロー削除

**DELETE** `/workflows/{id}`

指定されたIDのワークフローを削除します。

**パラメータ:**
- `id` (integer, required): ワークフローID

**レスポンス例:**
```json
{
  "success": true,
  "message": "ワークフローを削除しました"
}
```

#### 1.6 ノード追加

**POST** `/workflows/{id}/nodes`

指定されたワークフローにノードを追加します。

**パラメータ:**
- `id` (integer, required): ワークフローID

**リクエスト例:**
```json
{
  "node_type": "generative_ai",
  "config": {
    "prompt": "このテキストを要約してください",
    "model": "google/gemma-3n-e2b-it:free",
    "max_tokens": 1000,
    "temperature": 0.7
  }
}
```

**リクエストパラメータ:**
- `node_type` (string, required): ノードタイプ（`formatter`, `extract_text`, `generative_ai`）
- `config` (object, required): ノード設定

**ノードタイプ別の設定例:**

**FORMATTER:**
```json
{
  "node_type": "formatter",
  "config": {
    "format_type": "uppercase" // uppercase, lowercase, fullwidth, halfwidth
  }
}
```

**EXTRACT_TEXT:**
```json
{
  "node_type": "extract_text",
  "config": {
    "file_path": "/storage/uploads/document.pdf"
  }
}
```

**GENERATIVE_AI:**
```json
{
  "node_type": "generative_ai",
  "config": {
    "prompt": "このテキストを要約してください",
    "model": "google/gemma-3n-e2b-it:free",
    "max_tokens": 1000,
    "temperature": 0.7
  }
}
```

**レスポンス例:**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "workflow_id": 1,
    "node_type": "generative_ai",
    "config": {
      "prompt": "このテキストを要約してください",
      "model": "google/gemma-3n-e2b-it:free",
      "max_tokens": 1000,
      "temperature": 0.7
    },
    "order": 2,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "message": "ノードを追加しました"
}
```

#### 1.7 ワークフロー実行（非同期）

**POST** `/workflows/{id}/run`

指定されたワークフローを非同期で実行します。

**パラメータ:**
- `id` (integer, required): ワークフローID

**レスポンス例:**
```json
{
  "success": true,
  "data": {
    "message": "ワークフロー実行を開始しました",
    "session_id": "workflow_1_abc123def456",
    "status": "processing"
  },
  "message": "ワークフロー実行を開始しました"
}
```

#### 1.8 実行状況取得

**GET** `/workflows/execution/{sessionId}`

ワークフロー実行の状況を取得します。

**パラメータ:**
- `sessionId` (string, required): セッションID

**レスポンス例（実行中）:**
```json
{
  "success": true,
  "data": {
    "status": "processing",
    "message": "ワークフロー実行中..."
  },
  "message": "実行状況を取得しました"
}
```

**レスポンス例（完了）:**
```json
{
  "success": true,
  "data": {
    "status": "completed",
    "result": {
      "workflow_id": 1,
      "workflow_name": "サンプルワークフロー",
      "input_type": "text",
      "output_type": "text",
      "results": [
        {
          "node_id": 1,
          "node_type": "formatter",
          "input": "hello world",
          "output": "HELLO WORLD",
          "config": {
            "format_type": "uppercase"
          }
        }
      ],
      "final_result": "HELLO WORLD"
    }
  },
  "message": "実行結果を取得しました"
}
```

**レスポンス例（エラー）:**
```json
{
  "success": true,
  "data": {
    "status": "failed",
    "error": "ノード処理中にエラーが発生しました",
    "details": "詳細なエラー情報"
  },
  "message": "実行エラーを取得しました"
}
```

### 2. ファイル管理

#### 2.1 PDFファイルアップロード

**POST** `/files/upload`

PDFファイルをアップロードします。

**リクエスト:**
- Content-Type: `multipart/form-data`
- `file` (file, required): PDFファイル

**レスポンス例:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "original_name": "document.pdf",
    "file_path": "/storage/uploads/1754464759_document.pdf",
    "file_size": 1024000,
    "mime_type": "application/pdf",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "message": "ファイルをアップロードしました"
}
```

## エラーコード一覧

| コード | 説明 |
|--------|------|
| `VALIDATION_ERROR` | バリデーションエラー |
| `NOT_FOUND` | リソースが見つかりません |
| `WORKFLOW_EXECUTION_ERROR` | ワークフロー実行エラー |
| `FILE_UPLOAD_ERROR` | ファイルアップロードエラー |
| `NODE_PROCESSING_ERROR` | ノード処理エラー |
| `QUEUE_ERROR` | キュー処理エラー |

## ノードタイプ詳細

### FORMATTER

テキストの整形を行うノードです。

**設定パラメータ:**
- `format_type` (string, required): フォーマットタイプ
  - `uppercase`: 大文字に変換
  - `lowercase`: 小文字に変換
  - `fullwidth`: 半角を全角に変換
  - `halfwidth`: 全角を半角に変換

### EXTRACT_TEXT

PDFファイルからテキストを抽出するノードです。

**設定パラメータ:**
- `file_path` (string, required): PDFファイルパス

### GENERATIVE_AI

AIを使用してテキスト処理を行うノードです。

**設定パラメータ:**
- `prompt` (string, required): AIへの指示文
- `model` (string, optional): 使用するAIモデル（デフォルト: `google/gemma-3n-e2b-it:free`）
- `max_tokens` (integer, optional): 最大トークン数（デフォルト: 1000）
- `temperature` (float, optional): 創造性パラメータ（デフォルト: 0.7）

## 非同期処理の仕組み

1. **実行開始**: `POST /workflows/{id}/run`でワークフロー実行を開始
2. **セッションID取得**: レスポンスでセッションIDを取得
3. **状況監視**: `GET /workflows/execution/{sessionId}`で実行状況を定期的に確認
4. **結果取得**: 実行完了後に結果を取得

**推奨ポーリング間隔**: 1秒

## ファイルアップロード制限

- **対応形式**: PDFファイルのみ
- **最大サイズ**: 10MB
- **保存場所**: `storage/app/public/uploads/`
- **ファイル名**: タイムスタンプ_元ファイル名

## 開発者向け情報

### テスト実行

```bash
# 全テスト実行
make test

# ユニットテストのみ
make test-unit

# Featureテストのみ
make test-feature
```

### 環境変数

**バックエンド（backend/.env）:**
```
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

OPENROUTER_API_KEY=your-api-key-here
```

**フロントエンド（frontend/.env.local）:**
```
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

### ログ確認

```bash
# 全ログ
make logs

# バックエンドログのみ
docker-compose logs backend

# フロントエンドログのみ
docker-compose logs frontend
``` 