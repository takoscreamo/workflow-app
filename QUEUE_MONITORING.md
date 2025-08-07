# Queue Worker 監視・恒久対応ガイド

## 概要

このドキュメントでは、Queue Workerの監視と恒久対応について説明します。

## 問題の原因

### 1. Queue Workerの不安定性
- Queue Workerが時々正常に動作しなくなる
- ジョブが処理されない状態が発生
- 失敗したジョブの蓄積

### 2. 設定の問題
- 適切なオプションが設定されていない
- 自動再起動機能がない
- 監視機能がない

## 恒久対応策

### 1. Docker Compose設定の改善

```yaml
# docker-compose.yml
queue-worker:
  command: php artisan queue:work --tries=3 --timeout=300 --sleep=3 --max-jobs=1000 --max-time=3600
  restart: unless-stopped
```

**オプション説明:**
- `--tries=3`: 失敗時の再試行回数
- `--timeout=300`: ジョブのタイムアウト時間（秒）
- `--sleep=3`: ジョブがない場合の待機時間（秒）
- `--max-jobs=1000`: 最大処理ジョブ数
- `--max-time=3600`: 最大実行時間（秒）
- `restart: unless-stopped`: コンテナが停止した場合の自動再起動

### 2. ヘルスチェック機能

#### コマンド
```bash
# ヘルスチェック実行
make queue-health-check

# Queue Worker状態確認
make queue-status

# Queue Worker再起動
make queue-restart

# 失敗したジョブをクリア
make queue-clear-failed
```

#### API エンドポイント
```bash
# 状態確認
GET /api/queue/status

# 再起動
POST /api/queue/restart

# 失敗したジョブをクリア
POST /api/queue/clear-failed
```

### 3. 自動監視・復旧

#### Cronジョブ設定
- 5分ごとにヘルスチェック実行
- 失敗した場合に自動再起動
- 1時間ごとに失敗したジョブをクリア（10個以上の場合）

#### 監視項目
- Queue Workerプロセスの存在確認
- 失敗したジョブ数の監視
- Queueサイズの監視

### 4. ログ監視

#### 重要なログメッセージ
```
# 正常な場合
RunWorkflowJob.handle開始
ノード処理成功
ワークフロー実行完了

# 異常な場合
Undefined property: App\Domain\Entities\Node::$workflow_id
```

## 運用コマンド

### 日常的な監視
```bash
# 状態確認
make queue-status

# ヘルスチェック
make queue-health-check
```

### 問題発生時の対応
```bash
# Queue Worker再起動
make queue-restart

# 失敗したジョブをクリア
make queue-clear-failed

# 手動でQueue Worker再起動
make queue-restart-workers
```

### 緊急時の対応
```bash
# 全サービス再起動
make restart

# データベースリセット（注意: データが消えます）
make db-reset
```

## 監視ダッシュボード

### API レスポンス例
```json
{
  "queue_size": 0,
  "failed_jobs": 0,
  "active_workers": 1,
  "is_healthy": true,
  "timestamp": "2025-08-07T04:42:51.000000Z"
}
```

### 正常な状態
- `active_workers > 0`: Queue Workerが動作中
- `failed_jobs < 10`: 失敗したジョブが少ない
- `is_healthy: true`: 全体的に正常

### 異常な状態
- `active_workers = 0`: Queue Workerが停止
- `failed_jobs >= 10`: 失敗したジョブが多い
- `is_healthy: false`: 全体的に異常

## トラブルシューティング

### 1. Queue Workerが起動しない
```bash
# ログ確認
make logs

# 手動でQueue Worker起動
docker-compose exec backend php artisan queue:work --verbose
```

### 2. ジョブが処理されない
```bash
# Queue状態確認
make queue-status

# 失敗したジョブをクリア
make queue-clear-failed
```

### 3. メモリ不足
```bash
# システムリソース確認
docker stats

# 不要なコンテナ・イメージ削除
make clean
```

## 予防策

### 1. 定期的な監視
- 5分ごとのヘルスチェック
- 1時間ごとの失敗ジョブクリア
- 日次でのログ確認

### 2. リソース監視
- CPU使用率
- メモリ使用率
- ディスク使用率

### 3. バックアップ
- データベースの定期バックアップ
- 設定ファイルのバックアップ
- ログファイルのアーカイブ

## 設定ファイル

### 重要な設定
```env
# .env
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

### Queue Worker設定
```bash
# 推奨設定
php artisan queue:work \
  --tries=3 \
  --timeout=300 \
  --sleep=3 \
  --max-jobs=1000 \
  --max-time=3600
```

## まとめ

この恒久対応により、以下の効果が期待できます：

1. **自動復旧**: Queue Workerが停止した場合の自動再起動
2. **監視機能**: リアルタイムでの状態監視
3. **予防保全**: 定期的なヘルスチェックとクリーンアップ
4. **運用効率**: 簡単なコマンドでの管理

これらの対策により、Queue Workerの安定性が大幅に向上し、PDFテキスト抽出などの非同期処理が確実に動作するようになります。 