<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class QueueController extends Controller
{
    /**
     * Queue Workerの状態を取得
     */
    public function status()
    {
        try {
            $queueSize = Redis::lLen('queues:default');
            $failedJobs = Redis::lLen('queues:default:failed');

            // プロセスチェック
            $processes = shell_exec('ps aux | grep "php artisan queue:work" | grep -v grep');
            $workerCount = substr_count($processes, 'php artisan queue:work');

            $status = [
                'queue_size' => $queueSize,
                'failed_jobs' => $failedJobs,
                'active_workers' => $workerCount,
                'is_healthy' => $workerCount > 0 && $failedJobs < 10,
                'timestamp' => now()->toISOString()
            ];

            return response()->json($status);

        } catch (\Exception $e) {
            Log::error('Queue status check failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to check queue status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Queue Workerを再起動
     */
    public function restart()
    {
        try {
            // 現在のQueue Workerプロセスを停止
            shell_exec('pkill -f "php artisan queue:work"');
            sleep(2);

            // 新しいQueue Workerを起動
            $command = 'php artisan queue:work --tries=3 --timeout=300 --sleep=3 --max-jobs=1000 --max-time=3600 > /dev/null 2>&1 &';
            shell_exec($command);

            sleep(3);

            // プロセスが起動したかチェック
            $processes = shell_exec('ps aux | grep "php artisan queue:work" | grep -v grep');
            $workerCount = substr_count($processes, 'php artisan queue:work');

            if ($workerCount > 0) {
                Log::info("Queue workers restarted successfully. Active workers: {$workerCount}");
                return response()->json([
                    'message' => 'Queue workers restarted successfully',
                    'active_workers' => $workerCount
                ]);
            } else {
                Log::error('Failed to restart queue workers');
                return response()->json([
                    'error' => 'Failed to restart queue workers'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Queue restart failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Queue restart failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 失敗したジョブをクリア
     */
    public function clearFailed()
    {
        try {
            $failedJobs = Redis::lLen('queues:default:failed');

            if ($failedJobs > 0) {
                Artisan::call('queue:flush');
                Log::info("Cleared {$failedJobs} failed jobs");

                return response()->json([
                    'message' => "Cleared {$failedJobs} failed jobs"
                ]);
            } else {
                return response()->json([
                    'message' => 'No failed jobs to clear'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to clear failed jobs: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to clear failed jobs',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
