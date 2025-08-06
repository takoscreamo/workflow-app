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
│   │   │   └── Repositories/ # リポジトリインターフェース
│   │   ├── Usecase/          # ユースケース層
│   │   │   ├── DTOs/         # データ転送オブジェクト
│   │   │   └── WorkflowUsecase.php
│   │   ├── Infrastructure/   # インフラストラクチャ層
│   │   │   ├── Models/       # Eloquentモデル
│   │   │   └── Repositories/ # リポジトリ実装
│   │   └── Http/            # プレゼンテーション層
│   │       └── Controllers/  # コントローラー
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
   - ワークフロー実行（同期的な実装）

2. **フロントエンド**
   - ワークフロー一覧表示
   - ワークフロー作成フォーム
   - 編集・削除機能
   - 実行ボタン

3. **アーキテクチャ**
   - オニオンアーキテクチャ実装
   - ドメイン駆動設計
   - 依存性注入
   - クリーンアーキテクチャ

### 🔄 実装予定

1. **NodeType.EXTRACT_TEXT**
   - PDFファイルアップロード機能
   - `spatie/pdf-to-text`パッケージ使用
   - PDFからテキスト抽出

2. **NodeType.GENERATIVE_AI**
   - OpenAI API連携
   - プロンプト設定機能
   - モデル選択機能

3. **NodeType.FORMATTER**
   - テキスト整形機能
   - 大文字化・小文字化
   - 全角変換

## 📚 API仕様

| メソッド | エンドポイント                  | 概要                           |
|----------|----------------------------------|--------------------------------|
| GET      | `/api/workflows`                | ワークフロー一覧取得           |
| POST     | `/api/workflows`                | ワークフロー作成               |
| GET      | `/api/workflows/{id}`           | ワークフロー取得               |
| PUT      | `/api/workflows/{id}`           | ワークフロー更新               |
| DELETE   | `/api/workflows/{id}`           | ワークフロー削除               |
| POST     | `/api/workflows/{id}/nodes`     | ノード追加                     |
| POST     | `/api/workflows/{id}/run`       | ワークフロー実行               |

## 🛠️ 技術スタック

### バックエンド
- **Laravel 11** - PHP 8.2
- **オニオンアーキテクチャ** - クリーンアーキテクチャ
- **SQLite** - 開発環境用データベース
- **Redis 7** - キャッシュ・キュー
- **Docker** - コンテナ化

### フロントエンド
- **Next.js 13+** - Reactフレームワーク
- **TypeScript** - 型安全な開発
- **Tailwind CSS** - スタイリング
- **App Router** - 新しいルーティング

## 🏗️ アーキテクチャ詳細

### ドメイン層（Domain Layer）
- **Entities**: ビジネスロジックの中心となるエンティティ
- **Repositories**: データアクセスの抽象化インターフェース

### ユースケース層（Usecase Layer）
- **DTOs**: データ転送オブジェクト
- **WorkflowUsecase**: ワークフロー関連のビジネスロジック

### インフラストラクチャ層（Infrastructure Layer）
- **Models**: Eloquentモデル
- **Repositories**: リポジトリの実装

### プレゼンテーション層（Presentation Layer）
- **Controllers**: HTTPリクエストの処理

## 📝 開発メモ

### 重要な実装ポイント
- ノードの`config`はJSON形式で保存し、各ノードタイプ固有の設定を管理
- ワークフロー実行は非同期処理で実装予定
- ファイルアップロードは適切なバリデーションを実装予定
- エラーハンドリングを各層で適切に実装

### 進捗状況
- ✅ **Phase 1**: 基本画面実装、動作確認、データベース設計、マイグレーション実装
- 🔄 **Phase 2**: 3つのノードタイプ実装（進行中）
- ⏳ **Phase 3**: 非同期処理実装
- ⏳ **Phase 4**: ドキュメント

## 🚀 今後の予定

1. **NodeType実装** - 3つのノードタイプの実装
2. **非同期処理** - Laravel Queueを使用した非同期実行
3. **ファイルアップロード** - PDFファイルのアップロード機能
4. **テスト実装** - ユニットテスト・統合テスト
5. **ドキュメント** - API仕様書の作成

