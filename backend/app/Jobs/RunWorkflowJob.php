<?php

namespace App\Jobs;

use App\Usecase\WorkflowUsecase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RunWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5分のタイムアウト
    public $tries = 3; // 最大3回リトライ

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $workflowId,
        private ?string $sessionId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WorkflowUsecase $workflowUsecase): void
    {
        try {
            Log::info("ワークフロー実行開始", [
                'workflow_id' => $this->workflowId,
                'session_id' => $this->sessionId
            ]);

            $result = $workflowUsecase->runWorkflow($this->workflowId);

            Log::info("ワークフロー実行完了", [
                'workflow_id' => $this->workflowId,
                'session_id' => $this->sessionId,
                'result' => $result
            ]);

            // データベースに結果を保存
            if ($this->sessionId) {
                try {
                    DB::table('execution_results')->updateOrInsert(
                        ['session_id' => $this->sessionId],
                        [
                            'workflow_id' => $this->workflowId,
                            'result' => json_encode($result),
                            'status' => 'completed',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );

                    Log::info("実行結果をデータベースに保存完了", [
                        'session_id' => $this->sessionId,
                        'workflow_id' => $this->workflowId
                    ]);
                } catch (\Exception $e) {
                    Log::error("データベース保存エラー", [
                        'session_id' => $this->sessionId,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }

        } catch (\Exception $e) {
            Log::error("ワークフロー実行エラー", [
                'workflow_id' => $this->workflowId,
                'session_id' => $this->sessionId,
                'error' => $e->getMessage()
            ]);

            // エラー情報もデータベースに保存
            if ($this->sessionId) {
                try {
                    DB::table('execution_results')->updateOrInsert(
                        ['session_id' => $this->sessionId],
                        [
                            'workflow_id' => $this->workflowId,
                            'result' => json_encode(['error' => true, 'message' => $e->getMessage()]),
                            'status' => 'error',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );

                    Log::info("エラー情報をデータベースに保存完了", [
                        'session_id' => $this->sessionId,
                        'workflow_id' => $this->workflowId
                    ]);
                } catch (\Exception $dbError) {
                    Log::error("エラー情報のデータベース保存エラー", [
                        'session_id' => $this->sessionId,
                        'error' => $dbError->getMessage()
                    ]);
                }
            }

            throw $e; // ジョブを失敗としてマーク
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ワークフロー実行ジョブ失敗", [
            'workflow_id' => $this->workflowId,
            'session_id' => $this->sessionId,
            'error' => $exception->getMessage()
        ]);

        // 失敗情報をデータベースに保存
        if ($this->sessionId) {
            try {
                DB::table('execution_results')->updateOrInsert(
                    ['session_id' => $this->sessionId],
                    [
                        'workflow_id' => $this->workflowId,
                        'result' => json_encode(['error' => true, 'message' => 'ワークフロー実行に失敗しました: ' . $exception->getMessage()]),
                        'status' => 'error',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );

                Log::info("失敗情報をデータベースに保存完了", [
                    'session_id' => $this->sessionId,
                    'workflow_id' => $this->workflowId
                ]);
            } catch (\Exception $dbError) {
                Log::error("失敗情報のデータベース保存エラー", [
                    'session_id' => $this->sessionId,
                    'error' => $dbError->getMessage()
                ]);
            }
        }
    }
}
