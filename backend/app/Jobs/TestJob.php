<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $message
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("テストジョブ実行", [
            'message' => $this->message,
            'timestamp' => now()->toISOString()
        ]);

        // 処理をシミュレート（2秒待機）
        sleep(2);

        Log::info("テストジョブ完了", [
            'message' => $this->message,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("テストジョブ失敗", [
            'message' => $this->message,
            'error' => $exception->getMessage()
        ]);
    }
}
