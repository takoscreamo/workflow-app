<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class QueueHealthCheck extends Command
{
    protected $signature = 'queue:health-check';
    protected $description = 'Check if queue worker is running properly';

    public function handle()
    {
        try {
            // Queueの状態をチェック
            $queueSize = Redis::lLen('queues:default');
            $failedJobs = Redis::lLen('queues:default:failed');

            $this->info("Queue Status:");
            $this->info("- Pending jobs: {$queueSize}");
            $this->info("- Failed jobs: {$failedJobs}");

            // プロセスチェック
            $processes = shell_exec('ps aux | grep "php artisan queue:work" | grep -v grep');
            $workerCount = substr_count($processes, 'php artisan queue:work');

            $this->info("- Active workers: {$workerCount}");

            if ($workerCount === 0) {
                $this->error('No queue workers are running!');
                Log::error('Queue health check failed: No workers running');
                return 1;
            }

            if ($failedJobs > 10) {
                $this->warn("Too many failed jobs: {$failedJobs}");
                Log::warning("Queue health check warning: {$failedJobs} failed jobs");
            }

            $this->info('Queue health check passed');
            return 0;

        } catch (\Exception $e) {
            $this->error('Queue health check failed: ' . $e->getMessage());
            Log::error('Queue health check error: ' . $e->getMessage());
            return 1;
        }
    }
}
