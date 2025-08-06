# Workflow App

ユーザーが自由に定義できる「ワークフロー（処理の流れ）」を構築し、実行できるアプリケーションです。
ワークフローは複数の「ノード（処理単位）」で構成され、ノードを組み合わせてテキストを加工・生成・抽出できます。

## 🏗️ アーキテクチャ

- **バックエンド**: Laravel 11 (PHP 8.2)
- **フロントエンド**: Next.js 13+ (App Router)
- **データベース**: MySQL 8.0
- **キャッシュ/キュー**: Redis 7
- **コンテナ化**: Docker Compose

## 📁 プロジェクト構成

```
workflow-app/
├── backend/          # Laravel 11 アプリケーション
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

### 4. データベースのマイグレーション

```bash
# バックエンドコンテナに入る
docker-compose exec backend bash

# マイグレーションを実行
php artisan migrate
```

### 5. アプリケーションにアクセス

- **フロントエンド**: http://localhost:3000
- **バックエンドAPI**: http://localhost:8000
- **データベース**: localhost:3306
- **Redis**: localhost:6379

## 📋 ノードタイプ

### 1. extract_text
PDFファイルからテキストを抽出するノード

### 2. generative_ai
LLMを使用してテキスト生成を行うノード
- 設定可能なプロンプトとモデル
- OpenAI API対応

### 3. formatter
テキストの整形を行うノード
- 大文字化・小文字化
- 全角変換
- その他の整形ルール

## 🔧 開発

### バックエンド開発

```bash
# Laravelコンテナに入る
docker-compose exec backend bash

# アーティザンコマンドの実行
php artisan make:controller WorkflowController
php artisan make:model Workflow -m
```

### フロントエンド開発

```bash
# Next.jsコンテナに入る
docker-compose exec frontend bash

# 依存関係のインストール
npm install
```

## 📚 API仕様

| メソッド | エンドポイント                  | 概要                           |
|----------|----------------------------------|--------------------------------|
| POST     | `/api/workflows`                | ワークフロー作成               |
| GET      | `/api/workflows/{id}`           | ワークフロー取得               |
| POST     | `/api/workflows/{id}/nodes`     | ノード追加                     |
| POST     | `/api/workflows/{id}/run`       | ワークフロー非同期実行         |
| POST     | `/api/files/upload`             | PDFファイルアップロード        |

## 🛠️ 技術スタック

### バックエンド
- Laravel 11
- MySQL 8.0
- Redis 7
- spatie/pdf-to-text
- Laravel Queue

### フロントエンド
- Next.js 13+
- TypeScript
- Tailwind CSS
- App Router

## 📝 注意事項

- 初回起動時はデータベースのマイグレーションが必要です
- OpenAI APIを使用する場合は、`.env`ファイルにAPIキーを設定してください
- ファイルアップロード機能を使用する場合は、適切なストレージ設定が必要です

