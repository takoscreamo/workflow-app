# ワークフローアプリケーション - バックエンド課題実装タスク

## 🎯 必須要件（最優先で実装）

### 1. データストアによるデータ永続化
- [x] `Workflow`モデルとマイグレーション作成
  - `id`, `name` フィールド
- [x] `Node`モデルとマイグレーション作成
  - `id`, `workflow_id`, `node_type`, `config`（JSON）フィールド
- [x] データベースリレーション設定（Workflow hasMany Node）
- [x] 初期データのシーダー作成

### 2. 非同期処理によるWorkflow実行
- [x] `RunWorkflowJob`作成（Laravel Queue使用）
- [x] `RunNodeJob`作成（各ノード処理用）
- [x] Queue設定（Redis使用）
- [x] ジョブチェーン実装（`dispatch()->chain()`）
- [x] ワークフロー実行エンドポイントの非同期化

### 3. NodeType.GENERATIVE_AIの実装
- [x] LLM API連携機能実装（OpenRouter API使用）
- [x] プロンプト設定機能
- [x] モデル選択機能
- [x] その他のパラメータ設定機能
- [x] `generative_ai`ノード処理クラス作成

### 4. NodeType.FORMATTERの実装
- [x] テキスト整形機能実装
  - 大文字を小文字にする
  - 小文字を大文字にする
  - 半角を全角にする
  - 全角を半角にする
- [x] フォーマットルール設定機能
- [x] `formatter`ノード処理クラス作成

### 5. PDFファイルアップロード機能
- [x] PDFファイルアップロードエンドポイント実装
- [x] ファイル保存設定
- [x] ファイルバリデーション
- [x] NodeType.EXTRACT_TEXTの実装
  - `spatie/pdf-to-text`パッケージ使用
  - PDFからテキスト抽出機能

## 🔧 基本APIエンドポイント実装

### 1. ワークフロー管理API
- [x] `WorkflowController`作成
- [x] `POST /api/workflows` - ワークフロー作成
- [x] `GET /api/workflows/{id}` - ワークフロー取得
- [x] `PUT /api/workflows/{id}` - ワークフロー更新
- [x] `DELETE /api/workflows/{id}` - ワークフロー削除
- [x] `POST /api/workflows/{id}/nodes` - ノード追加
- [x] `POST /api/workflows/{id}/run` - ワークフロー実行（非同期処理）
- [x] `GET /api/workflows/execution/{sessionId}` - 実行状況取得

### 2. ファイルアップロードAPI
- [x] `FileController`作成
- [x] `POST /api/files/upload` - PDFファイルアップロード

### 3. リクエスト・レスポンスクラス
- [x] `CreateWorkflowDTO`作成
- [x] `UpdateWorkflowDTO`作成
- [x] `AddNodeDTO`作成
- [x] `WorkflowDetailResponse`作成

## 🏗️ アーキテクチャ実装

### 1. オニオンアーキテクチャ
- [x] Domain層（エンティティ、リポジトリインターフェース）
- [x] Usecase層（DTOs、ユースケース）
- [x] Infrastructure層（Eloquentモデル、リポジトリ実装）
- [x] Presentation層（コントローラー）
- [x] 依存性注入の設定

### 2. ドメイン駆動設計
- [x] `Workflow`エンティティ作成
- [x] `Node`エンティティ作成
- [x] `NodeType`列挙型作成
- [x] リポジトリパターン実装

## 🎨 フロントエンド実装（Next.js）

### 1. 基本構造
- [x] API通信ラッパー作成（`lib/api.ts`）
- [x] 型定義作成（`types/`）
  - `Workflow`, `Node`, `NodeType`インターフェース
  - リクエスト・レスポンス型定義

### 2. ページ実装
- [x] ワークフロー一覧画面（作成・編集・削除・実行）
- [x] ワークフロー詳細画面（ノード追加・一覧）
- [x] ノード設定画面（各ノードタイプ用）
- [x] 実行結果表示画面

### 3. コンポーネント実装
- [x] ワークフロー作成フォーム
- [x] ノード追加コンポーネント
- [x] ノード設定フォーム
  - `generative_ai`用（プロンプト、モデル設定）
  - `formatter`用（フォーマットルール設定）
  - `extract_text`用（PDFテキスト抽出設定）
- [x] ファイルアップロードコンポーネント
- [x] 実行ボタン・ステータス表示（非同期処理対応）

## 🔗 API連携・統合

### 1. フロントエンドとバックエンド連携
- [x] API通信ラッパー実装
- [x] エラーハンドリング
- [x] ローディング状態管理
- [x] CORS設定最終調整

### 2. 非同期処理のフロントエンド対応
- [x] ワークフロー実行状況の監視
- [x] 実行結果の取得・表示

## 🧪 テスト実装

### 1. バックエンドテスト
- [x] 基本的なテスト環境構築（PHPUnit設定）
- [x] `FormatterNodeProcessorTest`実装
  - 全角・半角変換テスト
  - 大文字・小文字変換テスト
  - フォーマットタイプ未指定時のテスト
- [x] `MbConvertKanaTest`実装
  - mb_convert_kana関数の各オプションテスト
  - 組み合わせテスト
- [x] `GenerativeAiNodeProcessorTest`実装
  - API通信テスト（Http::fake使用）
  - エラーハンドリングテスト
  - パラメータ設定テスト
- [x] `QueueSystemTest`実装
  - ジョブディスパッチテスト
  - リトライ・タイムアウト設定テスト
  - キャッシュ統合テスト
- [x] `TestJob`実装（Queueシステムテスト用）
- [x] テスト用コマンド実装（`test:queue`）
- [x] `WorkflowTest`実装
  - ワークフローCRUD操作のテスト
  - ノード追加テスト
  - バリデーションテスト

### 3. 統合テスト
- [x] ワークフロー実行の統合テスト（AsyncWorkflowTestとして実装済み）
- [x] ファイルアップロード統合テスト（FileUploadTestとして実装済み）

## 📚 ドキュメント

### 1. README更新
- [x] セットアップ・起動方法
- [x] エンドポイント仕様
- [x] アーキテクチャ説明
- [x] 実装状況の反映
- [x] Makefileコマンドの追加

### 2. API仕様書
- [x] エンドポイント詳細ドキュメント
- [x] リクエスト・レスポンス例
- [x] エラーコード一覧
- [x] ノードタイプ詳細説明
- [x] 非同期処理の仕組み説明

### 3. 開発者ガイド
- [x] 開発環境セットアップ
- [x] アーキテクチャ詳細説明
- [x] 開発フロー
- [x] テスト戦略
- [x] 主要ファイル説明

### 4. Makefile
- [x] 環境構築コマンド
- [x] テスト実行コマンド
- [x] アプリケーション管理コマンド
- [x] データベース操作コマンド
- [x] クリーンアップコマンド

## 🚀 オプション機能（将来の拡張）

### 1. DAG（有向非巡回グラフ）構造
- [ ] 複数の入力・出力対応
- [ ] ノード間の依存関係管理
- [ ] グラフ構造の可視化

### 2. AIエージェントノード
- [ ] 達成条件設定機能
- [ ] タスクプランニング機能
- [ ] 必要なツール判断機能
- [ ] 終了条件自動判断機能

---

## 📊 進捗管理

### 現在の状況
- ✅ Docker環境構築完了
- ✅ Laravel 11 + Next.js 13+ セットアップ完了
- ✅ 基本的なアプリケーション起動確認完了
- ✅ オニオンアーキテクチャ実装完了
- ✅ データベース設計・マイグレーション実装完了
- ✅ 基本APIエンドポイント実装完了
- ✅ フロントエンド基本画面実装完了
- ✅ **Phase 2**: 3つのノードタイプ実装完了（FORMATTER → EXTRACT_TEXT → GENERATIVE_AI）
- ✅ **Phase 3**: 非同期処理実装完了（Laravel Queue + Redis）
- ✅ **Phase 4**: バックエンドテスト実装完了（ユニットテスト）
- ✅ **Phase 5**: ドキュメント完成（Makefile、API仕様書、開発者ガイド）

### 実装優先順位
1. ✅ **Phase 1**: 基本画面実装、動作確認、データベース設計、マイグレーション実装
2. ✅ **Phase 2**: 3つのノードタイプ実装（FORMATTER → EXTRACT_TEXT → GENERATIVE_AI）
3. ✅ **Phase 3**: 非同期処理実装（Laravel Queue + Redis）
4. ✅ **Phase 4**: バックエンドテスト実装（完了）
5. ✅ **Phase 5**: ドキュメント完成（完了）

---
