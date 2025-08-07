# Workflow App

ユーザーが自由に定義できる「ワークフロー（処理の流れ）」を構築し、実行できるアプリケーションです。
ワークフローは複数の「ノード（処理単位）」で構成され、ノードを組み合わせてテキストを加工・生成・抽出できます。

## 画面イメージ
<img alt="Image" src="https://github.com/user-attachments/assets/65e14370-c282-4e5a-88dd-2581c9fcb34d" />

## 操作デモ動画(Youtube動画リンク)
https://youtu.be/hDZfLlGXatk


## 🚀 セットアップと起動

### 前提条件

- Docker Desktopがインストールされていること
- Makeコマンドが実行可能であること

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd workflow-app
```

### 2. セットアップと起動
初期化（セットアップ、起動、マイグレーション、シーダーを一括実行）
```bash
make init
```

### 3. アプリケーションにアクセス

- **フロントエンド**: http://localhost:3000
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

## 📚 詳細ドキュメント

- **[PROJECT_GUIDE.md](PROJECT_GUIDE.md)** - プロジェクトの包括的なガイド（アーキテクチャ、開発環境など）
- **[API.md](API.md)** - バックエンドAPIの詳細仕様


## ⚠️ 注意事項

1. **OpenRouter APIキー**: GENERATIVE_AIノードを使用する場合は、OpenRouter APIキーの設定が必要です
2. **ファイルアップロード**: PDFファイルのみアップロード可能（最大10MB）
3. **非同期処理**: ワークフロー実行は非同期処理のため、実行完了まで時間がかかる場合があります


## 🧪 テストとテスト実行方法

テストはPHPUnitを使用しています。テストを実行するには、以下のコマンドを実行してください。
```bash
make test
```
テストコードは **[backend/tests/Unit](backend/tests/Unit)** に配置されています。

## 生成AIツールの利用について
- 生成AIツールの利用については、 設計方針検討にChatGPT、実装にCursorを使用しました。
- [Task.md](Task.md)に実装の進捗を記載して、Cursorに指示プロンプトを渡す際に開発の全体像を把握してもらうようにしました。

## 作業ごとのかかった時間
- 要件確認、設計方針、技術選定: 1時間
- 実装: 7時間
  - アプリケーションの雛形作成: 1時間
  - データストアによるデータ永続化: 1時間
  - 非同期処理によるWorkflow実行: 2時間
  - NodeType.GENERATIVE_AIの実装: 1時間
  - NodeType.FORMATTERの実装: 1時間
  - NodeType.EXTRACT_TEXTの実装: 1時間
  - 有向非巡回グラフ(DAG)構造の実装: 未実装
  - AIエージェントノードの実装: 未実装
- テスト、バグ修正: 4時間
- ドキュメント: 2時間
- 合計: 14時間
