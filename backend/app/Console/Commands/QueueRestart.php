<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class QueueRestart extends Command
{
    protected $signature = 'queue:restart-workers';
    protected $description = 'Restart queue workers if they are not running properly';

    public function handle()
    {
        try {
            // 現在のQueue Workerプロセスを停止
            $this->info('Stopping existing queue workers...');
            shell_exec('pkill -f "php artisan queue:work"');
            sleep(2);

            // 失敗したジョブをクリア（オプション）
            if ($this->confirm('Clear failed jobs?')) {
                $this->call('queue:flush');
                $this->info('Failed jobs cleared');
            }

            // 新しいQueue Workerを起動
            $this->info('Starting new queue workers...');
            $command = 'php artisan queue:work --tries=3 --timeout=300 --sleep=3 --max-jobs=1000 --max-time=3600 > /dev/null 2>&1 &';
            shell_exec($command);

            sleep(3);

            // プロセスが起動したかチェック
            $processes = shell_exec('ps aux | grep "php artisan queue:work" | grep -v grep');
            $workerCount = substr_count($processes, 'php artisan queue:work');

            if ($workerCount > 0) {
                $this->info("Queue workers restarted successfully. Active workers: {$workerCount}");
                Log::info("Queue workers restarted successfully. Active workers: {$workerCount}");
                return 0;
            } else {
                $this->error('Failed to restart queue workers');
                Log::error('Failed to restart queue workers');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Queue restart failed: ' . $e->getMessage());
            Log::error('Queue restart error: ' . $e->getMessage());
            return 1;
        }
    }
}
