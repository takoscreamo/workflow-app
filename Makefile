# Workflow App - Makefile
# 環境構築、テスト実行、開発作業を簡単にするためのMakefile

.PHONY: help setup start stop restart logs test test-unit test-feature test-all clean build frontend-build backend-setup migrate seed install-deps

# デフォルトターゲット
help:
	@echo "Workflow App - 利用可能なコマンド:"
	@echo ""
	@echo "環境構築:"
	@echo "  make setup          - 初回セットアップ（環境変数設定、依存関係インストール）"
	@echo "  make install-deps   - 依存関係のインストール"
	@echo "  make backend-setup  - バックエンド環境設定"
	@echo ""
	@echo "アプリケーション管理:"
	@echo "  make start          - 全サービスを起動"
	@echo "  make stop           - 全サービスを停止"
	@echo "  make restart        - 全サービスを再起動"
	@echo "  make logs           - ログを表示"
	@echo ""
	@echo "データベース:"
	@echo "  make migrate        - マイグレーション実行"
	@echo "  make seed           - シーダー実行"
	@echo ""
	@echo "テスト:"
	@echo "  make test           - 全テスト実行"
	@echo "  make test-unit      - ユニットテスト実行"
	@echo "  make test-feature   - Featureテスト実行"
	@echo "  make test-all       - 全テスト実行（詳細出力）"
	@echo ""
	@echo "ビルド:"
	@echo "  make build          - フロントエンド・バックエンドをビルド"
	@echo "  make frontend-build - フロントエンドのみビルド"
	@echo ""
	@echo "クリーンアップ:"
	@echo "  make clean          - コンテナ・ボリューム・キャッシュを削除"

# 初回セットアップ
setup: install-deps backend-setup
	@echo "✅ セットアップ完了！"
	@echo "次のコマンドでアプリケーションを起動してください:"
	@echo "  make start"

# 依存関係のインストール
install-deps:
	@echo "📦 依存関係をインストール中..."
	@if [ ! -f backend/.env ]; then \
		cp backend/.env.example backend/.env; \
		echo "✅ バックエンド環境変数ファイルを作成しました"; \
	fi
	@if [ ! -f frontend/.env.local ]; then \
		echo "NEXT_PUBLIC_API_URL=http://localhost:8000/api" > frontend/.env.local; \
		echo "✅ フロントエンド環境変数ファイルを作成しました"; \
	fi
	@echo "✅ 依存関係のインストール完了"

# バックエンド環境設定
backend-setup:
	@echo "🔧 バックエンド環境を設定中..."
	@if [ ! -f backend/.env ]; then \
		echo "❌ backend/.env ファイルが見つかりません。make install-deps を先に実行してください"; \
		exit 1; \
	fi
	@echo "✅ バックエンド環境設定完了"

# アプリケーション起動
start:
	@echo "🚀 アプリケーションを起動中..."
	docker-compose up -d
	@echo "✅ アプリケーションが起動しました"
	@echo "🌐 フロントエンド: http://localhost:3000"
	@echo "🔗 バックエンドAPI: http://localhost:8000"
	@echo "📊 ログを確認するには: make logs"

# アプリケーション停止
stop:
	@echo "🛑 アプリケーションを停止中..."
	docker-compose down
	@echo "✅ アプリケーションが停止しました"

# アプリケーション再起動
restart: stop start

# ログ表示
logs:
	@echo "📋 ログを表示中..."
	docker-compose logs -f

# マイグレーション実行
migrate:
	@echo "🗄️ データベースマイグレーションを実行中..."
	docker-compose exec backend php artisan migrate
	@echo "✅ マイグレーション完了"

# シーダー実行
seed:
	@echo "🌱 シーダーを実行中..."
	docker-compose exec backend php artisan db:seed
	@echo "✅ シーダー完了"

# 全テスト実行
test:
	@echo "🧪 テストを実行中..."
	docker-compose exec backend php artisan test
	@echo "✅ テスト完了"

# ユニットテスト実行
test-unit:
	@echo "🧪 ユニットテストを実行中..."
	docker-compose exec backend php artisan test --testsuite=Unit
	@echo "✅ ユニットテスト完了"

# Featureテスト実行
test-feature:
	@echo "🧪 Featureテストを実行中..."
	docker-compose exec backend php artisan test --testsuite=Feature
	@echo "✅ Featureテスト完了"

# 全テスト実行（詳細出力）
test-all:
	@echo "🧪 全テストを実行中（詳細出力）..."
	docker-compose exec backend php artisan test --verbose
	@echo "✅ 全テスト完了"

# フロントエンド・バックエンドをビルド
build: frontend-build
	@echo "✅ ビルド完了"

# フロントエンドのみビルド
frontend-build:
	@echo "🔨 フロントエンドをビルド中..."
	docker-compose exec frontend npm run build
	@echo "✅ フロントエンドビルド完了"

# クリーンアップ
clean:
	@echo "🧹 クリーンアップを実行中..."
	docker-compose down -v
	docker system prune -f
	@echo "✅ クリーンアップ完了"

# 開発用コマンド
dev:
	@echo "🛠️ 開発モードで起動中..."
	docker-compose up -d
	@echo "✅ 開発環境が起動しました"
	@echo "🌐 フロントエンド: http://localhost:3000"
	@echo "🔗 バックエンドAPI: http://localhost:8000"

# バックエンドコンテナに入る
backend-shell:
	@echo "🐳 バックエンドコンテナに入ります..."
	docker-compose exec backend bash

# フロントエンドコンテナに入る
frontend-shell:
	@echo "🐳 フロントエンドコンテナに入ります..."
	docker-compose exec frontend bash

# データベースリセット
db-reset:
	@echo "🔄 データベースをリセット中..."
	docker-compose exec backend php artisan migrate:fresh --seed
	@echo "✅ データベースリセット完了"

# キャッシュクリア
cache-clear:
	@echo "🗑️ キャッシュをクリア中..."
	docker-compose exec backend php artisan cache:clear
	docker-compose exec backend php artisan config:clear
	docker-compose exec backend php artisan route:clear
	@echo "✅ キャッシュクリア完了"

# ログクリア
log-clear:
	@echo "🗑️ ログをクリア中..."
	docker-compose exec backend php artisan log:clear
	@echo "✅ ログクリア完了"

# アプリケーション状態確認
status:
	@echo "📊 アプリケーション状態:"
	@docker-compose ps
	@echo ""
	@echo "🌐 アクセス可能なURL:"
	@echo "  フロントエンド: http://localhost:3000"
	@echo "  バックエンドAPI: http://localhost:8000"
	@echo "  Redis: localhost:6379" 