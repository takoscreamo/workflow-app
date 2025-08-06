# Workflow App

ユーザーが自由に定義できる「ワークフロー（処理の流れ）」を構築し、実行できるアプリケーションです。
ワークフローは複数の「ノード（処理単位）」で構成され、ノードを組み合わせてテキストを加工・生成・抽出できます。

## 🏗️ アーキテクチャ

- **バックエンド**: Laravel 11 (PHP 8.2) + オニオンアーキテクチャ
- **フロントエンド**: Next.js 13+ (App Router)
- **データベース**: SQLite（開発環境）
- **キャッシュ/キュー**: Redis 7
- **コンテナ化**: Docker Compose

## 📁 プロジェクト構成

```
workflow-app/
├── backend/          # Laravel 11 アプリケーション
│   ├── app/
│   │   ├── Domain/           # ドメイン層
│   │   │   ├── Entities/     # エンティティ
│   │   │   ├── Repositories/ # リポジトリインターフェース
│   │   │   └── Services/     # ノード処理サービス
│   │   ├── Usecase/          # ユースケース層
│   │   │   ├── DTOs/         # データ転送オブジェクト
│   │   │   └── WorkflowUsecase.php
│   │   ├── Infrastructure/   # インフラストラクチャ層
│   │   │   ├── Models/       # Eloquentモデル
│   │   │   └── Repositories/ # リポジトリ実装
│   │   ├── Http/            # プレゼンテーション層
│   │   │   └── Controllers/  # コントローラー
│   │   └── Jobs/            # 非同期ジョブ
│   └── database/
├── frontend/         # Next.js アプリケーション
├── docker-compose.yml
└── README.md
```

## 🚀 セットアップと起動

### 前提条件

- Docker
- Docker Compose

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd workflow-app
```

### 2. 環境変数の設定

```bash
# バックエンドの環境変数を設定
cp backend/.env.example backend/.env

# OpenRouter APIキーを設定（オプション）
echo "OPENROUTER_API_KEY=your-api-key-here" >> backend/.env

# フロントエンドの環境変数を設定
echo "NEXT_PUBLIC_API_URL=http://localhost:8000/api" > frontend/.env.local
```

### 3. Docker Composeで起動

```bash
# 全サービスを起動
docker-compose up -d

# ログを確認
docker-compose logs -f
```

### 4. データベースのマイグレーションとシーダー

```bash
# バックエンドコンテナに入る
docker-compose exec backend bash

# マイグレーションを実行
php artisan migrate

# シーダーを実行（サンプルデータ作成）
php artisan db:seed
```

### 5. アプリケーションにアクセス

- **フロントエンド**: http://localhost:3000
- **バックエンドAPI**: http://localhost:8000
- **データベース**: SQLite（backend/database/database.sqlite）
- **Redis**: localhost:6379

## 📋 実装済み機能

### ✅ 完了済み

1. **基本CRUD操作**
   - ワークフロー作成・取得・更新・削除
   - ノード追加
   - ワークフロー実行（非同期処理）

2. **入力・出力機能** 🔄 **新機能**
   - **入力種別選択**: テキストまたはPDFファイル
   - **出力種別選択**: テキスト表示またはPDFダウンロード
   - **テキスト入力**: textareaで直接入力
   - **PDFファイルアップロード**: ファイル選択でPDFをアップロード
   - **テキスト出力**: モーダルで表示、コピー機能付き
   - **PDF出力**: TCPDFライブラリで日本語対応PDF生成・ダウンロード

3. **ノード処理システム** ✅ **Phase 2完了**
   - **FORMATTER**: テキスト整形（大文字化・小文字化・全角変換・半角変換）
   - **EXTRACT_TEXT**: PDFファイルからテキスト抽出（spatie/pdf-to-text使用）
   - **GENERATIVE_AI**: OpenRouter API連携（プロンプト・モデル・パラメータ設定）

4. **非同期処理システム** ✅ **Phase 3完了**
   - **Laravel Queue**: Redisを使用した非同期ジョブ処理
   - **RunWorkflowJob**: ワークフロー実行用ジョブ
   - **実行状況監視**: フロントエンドでの実行状況ポーリング
   - **エラーハンドリング**: ジョブ失敗時の適切な処理
   - **ローディング状態**: 実行中のUI表示

5. **フロントエンド**
   - ワークフロー一覧表示
   - ワークフロー作成・編集フォーム（入力・出力設定含む）
   - 編集・削除機能
   - ノード追加機能
   - 実行ボタン（非同期処理対応）
   - 実行結果表示（テキスト・PDF）

6. **アーキテクチャ**
   - オニオンアーキテクチャ実装
   - ドメイン駆動設計
   - 依存性注入
   - クリーンアーキテクチャ

7. **ファイルアップロード**
   - PDFファイルアップロード機能
   - ファイルバリデーション
   - 安全なファイル保存

### 🔄 実装予定

1. **テスト実装**
   - ユニットテスト・統合テスト

2. **ドキュメント**
   - API仕様書の作成

## 🔄 入力・出力機能詳細

### 入力機能
- **テキスト入力**: ワークフロー作成・編集時にtextareaでテキストを直接入力
- **PDFファイル入力**: ファイル選択でPDFをアップロード、自動的にファイルパスが保存される
- **入力データの管理**: ワークフローの初期入力データとして最初のノードに渡される

### 出力機能
- **テキスト出力**: 実行結果をモーダルで表示、クリップボードにコピー可能
- **PDF出力**: TCPDFライブラリを使用して日本語対応のPDFを生成・ダウンロード
- **出力形式の選択**: ワークフロー作成時に出力種別（テキスト/PDF）を選択可能

### 技術実装
- **TCPDFライブラリ**: 日本語フォント対応のPDF生成
- **Base64エンコーディング**: PDFバイナリデータの安全な転送
- **ファイルアップロード**: 適切なバリデーションとセキュリティ対策
- **フロントエンド**: 直感的なUIで入力・出力種別を選択

## 🔄 非同期処理システム詳細

### アーキテクチャ
- **Laravel Queue**: Redisドライバーを使用した非同期処理
- **RunWorkflowJob**: ワークフロー実行専用ジョブクラス
- **セッション管理**: 実行結果の一時保存と取得
- **エラーハンドリング**: ジョブ失敗時の適切な処理

### フロントエンド対応
- **実行状況監視**: 定期的なポーリングによる実行状況確認
- **ローディング状態**: 実行中のUI表示とボタン無効化
- **エラー表示**: 実行失敗時の適切なエラーメッセージ表示

### 技術実装
- **Redis Queue**: 高速なジョブ処理
- **セッションストレージ**: 実行結果の一時保存
- **ポーリング機能**: 1秒間隔での実行状況確認
- **タイムアウト設定**: 5分の実行タイムアウト

## 📚 API仕様

| メソッド | エンドポイント                  | 概要                           |
|----------|----------------------------------|--------------------------------|
| GET      | `/api/workflows`                | ワークフロー一覧取得           |
| POST     | `/api/workflows`                | ワークフロー作成               |
| GET      | `/api/workflows/{id}`           | ワークフロー取得               |
| PUT      | `/api/workflows/{id}`           | ワークフロー更新               |
| DELETE   | `/api/workflows/{id}`           | ワークフロー削除               |
| POST     | `/api/workflows/{id}/nodes`     | ノード追加                     |
| POST     | `/api/workflows/{id}/run`       | ワークフロー非同期実行         |
| GET      | `/api/workflows/execution/{sessionId}` | 実行状況取得 |
| POST     | `/api/files/upload`             | PDFファイルアップロード        |

### ワークフロー作成・更新時のリクエスト形式

```json
{
  "name": "ワークフロー名",
  "input_type": "text|pdf",
  "output_type": "text|pdf",
  "input_data": "入力テキストまたはファイルパス"
}
```

### 非同期実行のレスポンス形式

```json
{
  "message": "ワークフロー実行を開始しました",
  "session_id": "workflow_1_abc123def456",
  "status": "processing"
}
```

### 実行状況取得のレスポンス形式

```json
{
  "status": "completed",
  "result": {
    "workflow_id": 1,
    "workflow_name": "サンプルワークフロー",
    "input_type": "text",
    "output_type": "text",
    "results": [...],
    "final_result": "処理結果"
  }
}
```

## 🛠️ 技術スタック

### バックエンド
- **Laravel 11** - PHP 8.2
- **オニオンアーキテクチャ** - クリーンアーキテクチャ
- **SQLite** - 開発環境用データベース
- **Redis 7** - キャッシュ・キュー
- **Docker** - コンテナ化
- **spatie/pdf-to-text** - PDFテキスト抽出
- **OpenRouter API** - AI処理（OpenAI互換）
- **tecnickcom/tcpdf** - 日本語対応PDF生成
- **Laravel Queue** - 非同期処理

### フロントエンド
- **Next.js 13+** - Reactフレームワーク
- **TypeScript** - 型安全な開発
- **Tailwind CSS** - スタイリング
- **App Router** - 新しいルーティング

## 🏗️ アーキテクチャ詳細

### ドメイン層（Domain Layer）
- **Entities**: ビジネスロジックの中心となるエンティティ
- **Repositories**: データアクセスの抽象化インターフェース
- **Services**: ノード処理サービス（FormatterNodeProcessor、ExtractTextNodeProcessor、GenerativeAiNodeProcessor）

### ユースケース層（Usecase Layer）
- **DTOs**: データ転送オブジェクト
- **WorkflowUsecase**: ワークフロー関連のビジネスロジック

### インフラストラクチャ層（Infrastructure Layer）
- **Models**: Eloquentモデル
- **Repositories**: リポジトリの実装
- **External Services**: 外部API連携

### プレゼンテーション層（Presentation Layer）
- **Controllers**: HTTPリクエストの処理
- **API Routes**: RESTful APIエンドポイント

### 非同期処理層（Queue Layer）
- **Jobs**: 非同期ジョブクラス
- **Queue Workers**: ジョブ処理ワーカー
- **Redis**: ジョブキュー管理

## 🎯 ノード処理システム詳細

### FORMATTER ノード
- **機能**: テキストの整形処理
- **設定項目**: 
  - `format_type`: `uppercase`（大文字化）、`lowercase`（小文字化）、`fullwidth`（全角変換）、`halfwidth`（半角変換）
- **実装**: `FormatterNodeProcessor`クラス

### EXTRACT_TEXT ノード
- **機能**: PDFファイルからテキスト抽出
- **設定項目**: 
  - `file_path`: PDFファイルパス（自動設定）
- **実装**: `ExtractTextNodeProcessor`クラス（spatie/pdf-to-text使用）

### GENERATIVE_AI ノード
- **機能**: AIによるテキスト処理
- **設定項目**:
  - `prompt`: AIへの指示文
  - `model`: 使用するAIモデル（デフォルト: `google/gemma-3n-e2b-it:free`）
  - `max_tokens`: 最大トークン数（デフォルト: 1000）
  - `temperature`: 創造性パラメータ（デフォルト: 0.7）
- **実装**: `GenerativeAiNodeProcessor`クラス（OpenRouter API使用）

## 🔄 進捗状況

### ✅ 完了済み（Phase 1, 2 & 3）
1. **基本アーキテクチャ構築**
   - Docker環境構築
   - Laravel 11 + Next.js 13+ セットアップ
   - オニオンアーキテクチャ実装

2. **データベース設計・実装**
   - Workflow・Nodeモデル設計
   - マイグレーション・シーダー実装
   - リレーション設定

3. **基本API実装**
   - ワークフローCRUD操作
   - ノード追加機能
   - ファイルアップロード機能

4. **フロントエンド実装**
   - ワークフロー管理画面
   - ノード設定フォーム
   - 実行結果表示

5. **ノード処理システム実装**
   - 3つのノードタイプ完全実装
   - ファクトリーパターンによる動的処理
   - エラーハンドリング

6. **非同期処理システム実装** ✅ **Phase 3完了**
   - Laravel Queue + Redis設定
   - RunWorkflowJob実装
   - 実行状況監視機能
   - フロントエンド非同期対応
   - エラーハンドリング・リトライ機能

### ⏳ 実装予定（Phase 4以降）
1. **テスト実装**
   - ユニットテスト
   - 統合テスト
   - E2Eテスト

2. **ドキュメント完成**
   - API仕様書
   - 開発者ガイド

## 🚀 次のステップ

現在、Phase 3（非同期処理実装）が完了しました。次のPhase 4ではテスト実装を行い、システムの品質向上を図ります。

## 🧪 テスト方法

### Queueシステムのテスト

```bash
# バックエンドコンテナに入る
docker-compose exec backend bash

# テストジョブを実行
php artisan test:queue

# Queueワーカーのログを確認
docker-compose logs queue-worker
```

### 非同期処理の動作確認

1. フロントエンドでワークフローを作成
2. ノードを追加
3. 実行ボタンをクリック
4. 「実行中...」の表示を確認
5. 実行完了後に結果を確認

