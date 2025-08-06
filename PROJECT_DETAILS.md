# Workflow App - プロジェクト詳細

このドキュメントには、Workflow Appの詳細な実装情報、アーキテクチャ、進捗状況が含まれています。

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

## 📋 実装済み機能

### ✅ 完了済み

1. **基本CRUD操作**
   - ワークフロー作成・取得・更新・削除
   - ノード追加・削除
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
   - ノード追加・削除機能
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
   - ノード追加・削除機能
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

## 📚 ドキュメント

### 開発者向けドキュメント

- **[API仕様書](API.md)** - バックエンドAPIの詳細仕様
- **[OpenAPI仕様](openapi.yaml)** - OpenAPI 3.0形式のAPI仕様
- **[SwaggerUI](swagger-ui.html)** - インタラクティブなAPI仕様書
- **[開発者ガイド](DEVELOPER_GUIDE.md)** - 開発環境セットアップ、アーキテクチャ、開発フロー
- **[タスク管理](TASK.md)** - 実装タスクと進捗状況

### テスト方法

```bash
# 全テスト実行
make test

# ユニットテストのみ
make test-unit

# Featureテストのみ
make test-feature

# 詳細出力付きテスト
make test-all
```

### Queueシステムのテスト

```bash
# バックエンドコンテナに入る
make backend-shell

# テストジョブを実行
php artisan test:queue

# Queueワーカーのログを確認
make logs
```

### 非同期処理の動作確認

1. フロントエンドでワークフローを作成
2. ノードを追加
3. 実行ボタンをクリック
4. 「実行中...」の表示を確認
5. 実行完了後に結果を確認

## 🎯 プロジェクト完了状況

### ✅ 完了済み（Phase 1-4）
- **Phase 1**: 基本画面実装、動作確認、データベース設計、マイグレーション実装
- **Phase 2**: 3つのノードタイプ実装（FORMATTER → EXTRACT_TEXT → GENERATIVE_AI）
- **Phase 3**: 非同期処理実装（Laravel Queue + Redis）
- **Phase 4**: テスト実装（ユニットテスト・Featureテスト・統合テスト）
- **Phase 5**: ドキュメント完成（Makefile、API仕様書、開発者ガイド）

### 🎉 全機能実装完了

Workflow Appの全機能が実装完了しました！

- ✅ オニオンアーキテクチャ実装
- ✅ 3つのノードタイプ（FORMATTER、EXTRACT_TEXT、GENERATIVE_AI）
- ✅ 非同期処理システム（Laravel Queue + Redis）
- ✅ フロントエンド（Next.js + TypeScript）
- ✅ テスト環境（PHPUnit）
- ✅ ドキュメント完成（API仕様書、開発者ガイド、Makefile） 