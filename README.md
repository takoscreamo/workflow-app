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

# OpenAI APIキーを設定（オプション）
echo "OPENAI_API_KEY=your-api-key-here" >> backend/.env

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
   - ワークフロー実行（同期的な実装）

2. **入力・出力機能** 🔄 **新機能**
   - **入力種別選択**: テキストまたはPDFファイル
   - **出力種別選択**: テキスト表示またはPDFダウンロード
   - **テキスト入力**: textareaで直接入力
   - **PDFファイルアップロード**: ファイル選択でPDFをアップロード
   - **テキスト出力**: モーダルで表示、コピー機能付き
   - **PDF出力**: TCPDFライブラリで日本語対応PDF生成・ダウンロード

3. **ノード処理システム**
   - **FORMATTER**: テキスト整形（大文字化・小文字化・全角変換・半角変換）
   - **EXTRACT_TEXT**: PDFファイルからテキスト抽出
   - **GENERATIVE_AI**: OpenAI API連携（プロンプト・モデル・パラメータ設定）

4. **フロントエンド**
   - ワークフロー一覧表示
   - ワークフロー作成・編集フォーム（入力・出力設定含む）
   - 編集・削除機能
   - ノード追加機能
   - 実行ボタン
   - 実行結果表示（テキスト・PDF）

5. **アーキテクチャ**
   - オニオンアーキテクチャ実装
   - ドメイン駆動設計
   - 依存性注入
   - クリーンアーキテクチャ

6. **ファイルアップロード**
   - PDFファイルアップロード機能
   - ファイルバリデーション
   - 安全なファイル保存

### 🔄 実装予定

1. **非同期処理**
   - Laravel Queueを使用した非同期実行
   - ワークフロー実行状況の監視

2. **テスト実装**
   - ユニットテスト・統合テスト

3. **ドキュメント**
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

## 🛠️ 技術スタック

### バックエンド
- **Laravel 11** - PHP 8.2
- **オニオンアーキテクチャ** - クリーンアーキテクチャ
- **SQLite** - 開発環境用データベース
- **Redis 7** - キャッシュ・キュー
- **Docker** - コンテナ化
- **spatie/pdf-to-text** - PDFテキスト抽出
- **openai-php/client** - OpenAI API連携
- **tecnickcom/tcpdf** - 日本語対応PDF生成

### フロントエンド
- **Next.js 13+** - Reactフレームワーク
- **TypeScript** - 型安全な開発
- **Tailwind CSS** - スタイリング
- **App Router** - 新しいルーティング

## 🏗️ アーキテクチャ詳細

### ドメイン層（Domain Layer）
- **Entities**: ビジネスロジックの中心となるエンティティ
- **Repositories**: データアクセスの抽象化インターフェース
- **Services**: ノード処理のビジネスロジック

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
- ファイルアップロードは適切なバリデーションを実装
- エラーハンドリングを各層で適切に実装
- 入力・出力機能により、ユーザーは柔軟にデータ形式を選択可能

### 進捗状況
- ✅ **Phase 1**: 基本画面実装、動作確認、データベース設計、マイグレーション実装
- ✅ **Phase 2**: 3つのノードタイプ実装完了
- ✅ **Phase 3**: 入力・出力機能実装完了
- ⏳ **Phase 4**: 非同期処理実装
- ⏳ **Phase 5**: ドキュメント

## 🚀 今後の予定

1. **非同期処理** - Laravel Queueを使用した非同期実行
2. **テスト実装** - ユニットテスト・統合テスト
3. **ドキュメント** - API仕様書の作成
4. **パフォーマンス最適化** - キャッシュ・最適化
5. **セキュリティ強化** - 認証・認可機能
6. **UI/UX改善** - より直感的なインターフェース

