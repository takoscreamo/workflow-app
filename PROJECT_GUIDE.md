# Workflow App - プロジェクトガイド

---

## 📖 概要

Workflow Appは、ノードベースのワークフローエンジンです。ユーザーは複数のノードを組み合わせて、テキスト処理やAI処理を行うワークフローを作成・実行できます。

### 主な特徴

- **ノードベース処理**: FORMATTER、EXTRACT_TEXT、GENERATIVE_AIの3種類のノード
- **非同期実行**: Laravel Queue + Redisによる高性能な非同期処理
- **ファイル対応**: PDFファイルのアップロード・テキスト抽出
- **AI連携**: OpenRouter APIを使用したAI処理
- **モダンアーキテクチャ**: オニオンアーキテクチャによる保守性の高い設計

---

## 🏗️ アーキテクチャ

### 技術スタック

- **バックエンド**: Laravel 11 (PHP 8.2) + オニオンアーキテクチャ
- **フロントエンド**: Next.js 13+ (App Router) + TypeScript
- **データベース**: SQLite（開発環境）
- **キャッシュ/キュー**: Redis 7
- **コンテナ化**: Docker Compose
- **テスト**: PHPUnit

### アーキテクチャ原則

- **オニオンアーキテクチャ**: 依存関係の方向を制御
- **ドメイン駆動設計**: ビジネスロジックの明確化
- **依存性注入**: テスタビリティの向上
- **レイヤー分離**: 責務の明確化

---

## 📁 プロジェクト構成

```
workflow-app/
├── backend/                    # Laravel 11 アプリケーション
│   ├── app/
│   │   ├── Domain/            # ドメイン層（ビジネスロジック）
│   │   │   ├── Entities/      # エンティティ
│   │   │   ├── Repositories/  # リポジトリインターフェース
│   │   │   └── Services/      # ノード処理サービス
│   │   ├── Usecase/           # ユースケース層
│   │   │   ├── DTOs/          # データ転送オブジェクト
│   │   │   └── WorkflowUsecase.php
│   │   ├── Infrastructure/    # インフラストラクチャ層
│   │   │   ├── Models/        # Eloquentモデル
│   │   │   └── Repositories/  # リポジトリ実装
│   │   ├── Http/              # プレゼンテーション層
│   │   │   └── Controllers/   # コントローラー
│   │   └── Jobs/              # 非同期ジョブ
│   ├── database/
│   │   ├── migrations/        # データベースマイグレーション
│   │   └── seeders/           # シーダー
│   └── tests/                 # テスト
├── frontend/                   # Next.js アプリケーション
│   ├── src/
│   │   ├── app/               # App Router
│   │   ├── components/        # Reactコンポーネント
│   │   ├── hooks/             # カスタムフック
│   │   ├── lib/               # ユーティリティ
│   │   └── types/             # TypeScript型定義
│   └── public/                # 静的ファイル
├── docker-compose.yml         # Docker Compose設定
├── Makefile                   # 開発用コマンド
└── README.md                  # プロジェクト概要
```

---

## ✅ 実装済み機能

### 1. 基本CRUD操作
- ✅ ワークフロー作成・取得・更新・削除
- ✅ ノード追加・削除
- ✅ ワークフロー実行（非同期処理）

### 2. 入力・出力機能
- ✅ **入力種別選択**: テキストまたはPDFファイル
- ✅ **出力種別選択**: テキスト表示またはPDFダウンロード
- ✅ **テキスト入力**: textareaで直接入力
- ✅ **PDFファイルアップロード**: ファイル選択でPDFをアップロード
- ✅ **テキスト出力**: モーダルで表示、コピー機能付き
- ✅ **PDF出力**: TCPDFライブラリで日本語対応PDF生成・ダウンロード

### 3. ノード処理システム
- ✅ **FORMATTER**: テキスト整形（大文字化・小文字化・全角変換・半角変換）
- ✅ **EXTRACT_TEXT**: PDFファイルからテキスト抽出（spatie/pdf-to-text使用）
- ✅ **GENERATIVE_AI**: OpenRouter API連携（プロンプト・モデル・パラメータ設定）

### 4. 非同期処理システム
- ✅ **Laravel Queue**: Redisを使用した非同期ジョブ処理
- ✅ **RunWorkflowJob**: ワークフロー実行用ジョブ
- ✅ **実行状況監視**: フロントエンドでの実行状況ポーリング
- ✅ **エラーハンドリング**: ジョブ失敗時の適切な処理
- ✅ **ローディング状態**: 実行中のUI表示

### 5. フロントエンド
- ✅ ワークフロー一覧表示
- ✅ ワークフロー作成・編集フォーム（入力・出力設定含む）
- ✅ 編集・削除機能
- ✅ ノード追加・削除機能
- ✅ 実行ボタン（非同期処理対応）
- ✅ 実行結果表示（テキスト・PDF）

### 6. アーキテクチャ
- ✅ オニオンアーキテクチャ実装
- ✅ ドメイン駆動設計
- ✅ 依存性注入
- ✅ クリーンアーキテクチャ

### 7. ファイルアップロード
- ✅ PDFファイルアップロード機能
- ✅ ファイルバリデーション
- ✅ 安全なファイル保存

### 8. テスト環境
- ✅ PHPUnit設定
- ✅ ユニットテスト（FormatterNodeProcessorTest、GenerativeAiNodeProcessorTest等）
- ✅ Featureテスト（WorkflowTest、FileUploadTest、AsyncWorkflowTest）
- ✅ 統合テスト
- ✅ Queueシステムテスト

### 9. ドキュメント
- ✅ README.md（プロジェクト概要）
- ✅ API.md（API仕様書）
- ✅ OpenAPI仕様書（openapi.yaml）
- ✅ SwaggerUI（swagger-ui.html）
- ✅ Makefile（開発用コマンド集）

---

## 🚀 開発環境セットアップ

### 前提条件

- Docker
- Docker Compose
- Git

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd workflow-app
```

### 2. 初回セットアップ

```bash
# 初回セットアップ（環境変数設定、依存関係インストール）
make setup

# アプリケーション起動
make start

# データベースマイグレーションとシーダー
make migrate
make seed
```

### 3. 開発環境の確認

```bash
# アプリケーション状態確認
make status

# ログ確認
make logs
```

---

## 🛠️ 開発用コマンド

### 基本コマンド

```bash
# ヘルプ表示
make help

# アプリケーション管理
make start          # 起動
make stop           # 停止
make restart        # 再起動
make logs           # ログ表示

# データベース
make migrate        # マイグレーション
make seed           # シーダー
make db-reset       # データベースリセット

# テスト
make test           # 全テスト
make test-unit      # ユニットテスト
make test-feature   # Featureテスト
make test-all       # 詳細出力付きテスト

# クリーンアップ
make clean          # コンテナ・ボリューム削除
make cache-clear    # キャッシュクリア
make log-clear      # ログクリア
```

### 開発用コマンド

```bash
# コンテナに入る
make backend-shell   # バックエンドコンテナ
make frontend-shell  # フロントエンドコンテナ

# 開発モード
make dev             # 開発モードで起動
```

---

## 🏗️ アーキテクチャ詳細

### オニオンアーキテクチャ

#### 1. ドメイン層（Domain Layer）

ビジネスロジックの中心となる層です。外部依存を持たず、純粋なビジネスルールを定義します。

**主要クラス:**
- `Workflow` - ワークフローエンティティ
- `Node` - ノードエンティティ
- `NodeType` - ノードタイプ列挙型
- `NodeProcessorInterface` - ノード処理インターフェース

**実装例:**
```php
// app/Domain/Entities/Workflow.php
class Workflow
{
    public function __construct(
        private int $id,
        private string $name,
        private string $inputType,
        private string $outputType,
        private string $inputData,
        private array $nodes = []
    ) {}

    public function addNode(Node $node): void
    {
        $this->nodes[] = $node;
    }

    public function execute(string $input): array
    {
        // ビジネスロジック
    }
}
```

#### 2. ユースケース層（Usecase Layer）

アプリケーションのユースケースを定義する層です。ドメイン層とインフラストラクチャ層の橋渡しをします。

**主要クラス:**
- `WorkflowUsecase` - ワークフロー関連のユースケース
- `CreateWorkflowDTO` - ワークフロー作成用DTO
- `UpdateWorkflowDTO` - ワークフロー更新用DTO

**実装例:**
```php
// app/Usecase/WorkflowUsecase.php
class WorkflowUsecase
{
    public function __construct(
        private WorkflowRepositoryInterface $workflowRepository
    ) {}

    public function createWorkflow(CreateWorkflowDTO $dto): Workflow
    {
        $workflow = new Workflow(
            id: 0,
            name: $dto->name,
            inputType: $dto->inputType,
            outputType: $dto->outputType,
            inputData: $dto->inputData
        );

        return $this->workflowRepository->save($workflow);
    }
}
```

#### 3. インフラストラクチャ層（Infrastructure Layer）

外部依存（データベース、API等）の実装を行う層です。

**主要クラス:**
- `WorkflowModel` - Eloquentモデル
- `WorkflowRepository` - リポジトリ実装
- `NodeProcessorFactory` - ノード処理ファクトリー

**実装例:**
```php
// app/Infrastructure/Repositories/WorkflowRepository.php
class WorkflowRepository implements WorkflowRepositoryInterface
{
    public function save(Workflow $workflow): Workflow
    {
        $model = WorkflowModel::create([
            'name' => $workflow->getName(),
            'input_type' => $workflow->getInputType(),
            'output_type' => $workflow->getOutputType(),
            'input_data' => $workflow->getInputData(),
        ]);

        return $this->toEntity($model);
    }
}
```

#### 4. プレゼンテーション層（Presentation Layer）

HTTPリクエストの処理を行う層です。

**主要クラス:**
- `WorkflowController` - ワークフローコントローラー
- `FileController` - ファイルアップロードコントローラー

**実装例:**
```php
// app/Http/Controllers/WorkflowController.php
class WorkflowController extends Controller
{
    public function __construct(
        private WorkflowUsecase $workflowUsecase
    ) {}

    public function store(CreateWorkflowRequest $request): JsonResponse
    {
        $dto = new CreateWorkflowDTO(
            name: $request->name,
            inputType: $request->input_type,
            outputType: $request->output_type,
            inputData: $request->input_data
        );

        $workflow = $this->workflowUsecase->createWorkflow($dto);

        return response()->json([
            'success' => true,
            'data' => $workflow,
            'message' => 'ワークフローを作成しました'
        ]);
    }
}
```

### 非同期処理層（Queue Layer）

- **Jobs**: 非同期ジョブクラス
- **Queue Workers**: ジョブ処理ワーカー
- **Redis**: ジョブキュー管理

---

## 🎯 ノード処理システム

### ノードタイプ

#### 1. FORMATTER ノード
- **機能**: テキストの整形処理
- **設定項目**: 
  - `format_type`: `uppercase`（大文字化）、`lowercase`（小文字化）、`fullwidth`（全角変換）、`halfwidth`（半角変換）
- **実装**: `FormatterNodeProcessor`クラス

#### 2. EXTRACT_TEXT ノード
- **機能**: PDFファイルからテキスト抽出
- **設定項目**: 
  - `file_path`: PDFファイルパス（自動設定）
- **実装**: `ExtractTextNodeProcessor`クラス（spatie/pdf-to-text使用）

#### 3. GENERATIVE_AI ノード
- **機能**: AIによるテキスト処理
- **設定項目**:
  - `prompt`: AIへの指示文
  - `model`: 使用するAIモデル（デフォルト: `google/gemma-3n-e2b-it:free`）
  - `max_tokens`: 最大トークン数（デフォルト: 1000）
  - `temperature`: 創造性パラメータ（デフォルト: 0.7）
- **実装**: `GenerativeAiNodeProcessor`クラス（OpenRouter API使用）

### ファクトリーパターン

```php
// app/Domain/Services/NodeProcessorFactory.php
class NodeProcessorFactory
{
    public function create(string $nodeType): NodeProcessorInterface
    {
        return match ($nodeType) {
            'formatter' => new FormatterNodeProcessor(),
            'extract_text' => new ExtractTextNodeProcessor(),
            'generative_ai' => new GenerativeAiNodeProcessor(),
            default => throw new InvalidArgumentException("Unknown node type: {$nodeType}")
        };
    }
}
```

---

## 🔄 非同期処理システム

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

### 入力・出力機能詳細

#### 入力機能
- **テキスト入力**: ワークフロー作成・編集時にtextareaでテキストを直接入力
- **PDFファイル入力**: ファイル選択でPDFをアップロード、自動的にファイルパスが保存される
- **入力データの管理**: ワークフローの初期入力データとして最初のノードに渡される

#### 出力機能
- **テキスト出力**: 実行結果をモーダルで表示、クリップボードにコピー可能
- **PDF出力**: TCPDFライブラリを使用して日本語対応のPDFを生成・ダウンロード
- **出力形式の選択**: ワークフロー作成時に出力種別（テキスト/PDF）を選択可能

#### 技術実装
- **TCPDFライブラリ**: 日本語フォント対応のPDF生成
- **Base64エンコーディング**: PDFバイナリデータの安全な転送
- **ファイルアップロード**: 適切なバリデーションとセキュリティ対策
- **フロントエンド**: 直感的なUIで入力・出力種別を選択

---

## 🧪 テスト

### テスト実行

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

### テスト例

**ユニットテスト:**
```php
// tests/Unit/FormatterNodeProcessorTest.php
class FormatterNodeProcessorTest extends TestCase
{
    public function test_uppercase_conversion(): void
    {
        $processor = new FormatterNodeProcessor();
        $result = $processor->process('hello world', ['format_type' => 'uppercase']);
        
        $this->assertEquals('HELLO WORLD', $result);
    }
}
```

**Featureテスト:**
```php
// tests/Feature/WorkflowTest.php
class WorkflowTest extends TestCase
{
    public function test_can_create_workflow(): void
    {
        $response = $this->postJson('/api/workflows', [
            'name' => 'Test Workflow',
            'input_type' => 'text',
            'output_type' => 'text',
            'input_data' => 'test input'
        ]);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }
}
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

---
