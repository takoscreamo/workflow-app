<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Queue Workerのヘルスチェックを5分ごとに実行
        $schedule->command('queue:health-check')
                ->everyFiveMinutes()
                ->onFailure(function () {
                    // ヘルスチェックが失敗した場合、Queue Workerを再起動
                    Log::error('Queue health check failed, attempting restart');
                    $this->call('queue:restart-workers');
                });

        // 失敗したジョブを1時間ごとにクリア
        $schedule->command('queue:flush')
                ->hourly()
                ->when(function () {
                    // 失敗したジョブが10個以上ある場合のみ実行
                    return \Illuminate\Support\Facades\Redis::lLen('queues:default:failed') > 10;
                });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
