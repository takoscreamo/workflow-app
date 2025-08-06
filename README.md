# Workflow App

ユーザーが自由に定義できる「ワークフロー（処理の流れ）」を構築し、実行できるアプリケーションです。
ワークフローは複数の「ノード（処理単位）」で構成され、ノードを組み合わせてテキストを加工・生成・抽出できます。

## 🚀 セットアップと起動

### 前提条件

- Docker
- Docker Compose
- Make（オプション）

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd workflow-app
```

### 2. セットアップと起動

```bash
# 完全初期化（セットアップ、起動、マイグレーション、シーダーを一括実行）
make init
```

### 3. アプリケーションにアクセス

- **フロントエンド**: http://localhost:3000
- **バックエンドAPI**: http://localhost:8000
- **SwaggerUI**: http://localhost:8000/swagger

## 📋 エンドポイント仕様

| メソッド | エンドポイント                  | 概要                           |
|----------|----------------------------------|--------------------------------|
| GET      | `/api/workflows`                | ワークフロー一覧取得           |
| POST     | `/api/workflows`                | ワークフロー作成               |
| GET      | `/api/workflows/{id}`           | ワークフロー取得               |
| PUT      | `/api/workflows/{id}`           | ワークフロー更新               |
| DELETE   | `/api/workflows/{id}`           | ワークフロー削除               |
| POST     | `/api/workflows/{id}/nodes`     | ノード追加                     |
| DELETE   | `/api/workflows/{workflowId}/nodes/{nodeId}` | ノード削除 |
| POST     | `/api/workflows/{id}/run`       | ワークフロー非同期実行         |
| GET      | `/api/workflows/execution/{sessionId}` | 実行状況取得 |
| POST     | `/api/files/upload`             | PDFファイルアップロード        |
| GET      | `/api/health`                   | ヘルスチェック                 |

詳細なAPI仕様については **[API.md](API.md)** または **[SwaggerUI](http://localhost:8000/swagger)** を参照してください。

## 🔧 ノードタイプ

- **FORMATTER**: テキスト整形（大文字化・小文字化・全角変換・半角変換）
- **EXTRACT_TEXT**: PDFファイルからテキスト抽出
- **GENERATIVE_AI**: AIによるテキスト処理（OpenRouter API連携）

## 📚 詳細ドキュメント

- **[PROJECT_GUIDE.md](PROJECT_GUIDE.md)** - プロジェクトの包括的なガイド（アーキテクチャ、開発環境、API仕様、進捗状況）
- **[API.md](API.md)** - バックエンドAPIの詳細仕様

## ⚠️ 注意事項

1. **OpenRouter APIキー**: GENERATIVE_AIノードを使用する場合は、OpenRouter APIキーの設定が必要です
2. **ファイルアップロード**: PDFファイルのみアップロード可能（最大10MB）
3. **非同期処理**: ワークフロー実行は非同期処理のため、実行完了まで時間がかかる場合があります

