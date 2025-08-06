# Workflow App - 開発者ガイド

## 概要

このドキュメントは、Workflow Appの開発者向けガイドです。開発環境のセットアップ、アーキテクチャ、開発フローについて説明します。

## 🏗️ アーキテクチャ概要

### 技術スタック

- **バックエンド**: Laravel 11 (PHP 8.2)
- **フロントエンド**: Next.js 13+ (TypeScript)
- **データベース**: SQLite（開発環境）
- **キャッシュ/キュー**: Redis 7
- **コンテナ化**: Docker Compose
- **アーキテクチャ**: オニオンアーキテクチャ（クリーンアーキテクチャ）

### プロジェクト構造

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

### ノード処理システム

#### ノードタイプ

1. **FORMATTER** - テキスト整形
2. **EXTRACT_TEXT** - PDFテキスト抽出
3. **GENERATIVE_AI** - AI処理

#### ファクトリーパターン

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

## 🧪 テスト戦略

### テストピラミッド

1. **ユニットテスト** - 個別のクラス・メソッド
2. **Featureテスト** - 機能単位のテスト
3. **統合テスト** - システム全体のテスト

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

## 🔄 開発フロー

### 1. 機能開発

```bash
# 1. 開発環境起動
make start

# 2. 新しいブランチ作成
git checkout -b feature/new-feature

# 3. 開発・テスト
make test

# 4. コミット
git add .
git commit -m "feat: add new feature"

# 5. プルリクエスト作成
git push origin feature/new-feature
```

### 2. デバッグ

```bash
# ログ確認
make logs

# バックエンドコンテナに入る
make backend-shell

# キャッシュクリア
make cache-clear

# データベースリセット
make db-reset
```

### 3. パフォーマンス確認

```bash
# アプリケーション状態確認
make status

# ログ監視
make logs

# テスト実行時間確認
make test-all
```

## 📚 主要なファイル

### バックエンド

- `app/Domain/Entities/Workflow.php` - ワークフローエンティティ
- `app/Domain/Entities/Node.php` - ノードエンティティ
- `app/Domain/Services/NodeProcessorFactory.php` - ノード処理ファクトリー
- `app/Usecase/WorkflowUsecase.php` - ワークフロー関連ユースケース
- `app/Http/Controllers/WorkflowController.php` - ワークフローコントローラー
- `app/Jobs/RunWorkflowJob.php` - ワークフロー実行ジョブ

### フロントエンド

- `src/components/workflow/WorkflowList.tsx` - ワークフロー一覧
- `src/components/workflow/WorkflowForm.tsx` - ワークフロー作成・編集
- `src/components/node/NodeForm.tsx` - ノード設定
- `src/hooks/useWorkflows.ts` - ワークフロー関連フック
- `src/lib/api.ts` - API通信ラッパー

## 🔧 設定ファイル

### 環境変数

**バックエンド（backend/.env）:**
```env
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
```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

### Docker設定

**docker-compose.yml:**
```yaml
version: '3.8'
services:
  backend:
    build: ./backend
    ports:
      - "8000:80"
    volumes:
      - ./backend:/var/www/html
    depends_on:
      - redis

  frontend:
    build: ./frontend
    ports:
      - "3000:3000"
    volumes:
      - ./frontend:/app
    environment:
      - NEXT_PUBLIC_API_URL=http://localhost:8000/api

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
```

## 🚀 デプロイメント

### 開発環境

```bash
# 開発環境起動
make dev

# テスト実行
make test

# アプリケーション確認
make status
```

### 本番環境（将来の拡張）

```bash
# 本番環境用ビルド
make build

# 本番環境デプロイ
make deploy
```

## 📖 参考資料

- [Laravel 11 ドキュメント](https://laravel.com/docs/11.x)
- [Next.js 13+ ドキュメント](https://nextjs.org/docs)
- [オニオンアーキテクチャ](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [Docker Compose ドキュメント](https://docs.docker.com/compose/)

## 🤝 コントリビューション

1. フォークを作成
2. 機能ブランチを作成 (`git checkout -b feature/amazing-feature`)
3. 変更をコミット (`git commit -m 'feat: add amazing feature'`)
4. ブランチにプッシュ (`git push origin feature/amazing-feature`)
5. プルリクエストを作成

## 📝 ライセンス

このプロジェクトはMITライセンスの下で公開されています。 