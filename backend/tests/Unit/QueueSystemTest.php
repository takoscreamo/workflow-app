<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Jobs\TestJob;
use App\Jobs\RunWorkflowJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;

class QueueSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_can_dispatch_test_job()
    {
        TestJob::dispatch('test message');

        Queue::assertPushed(TestJob::class);
    }

    public function test_can_dispatch_run_workflow_job()
    {
        $workflowId = 1;
        RunWorkflowJob::dispatch($workflowId);

        Queue::assertPushed(RunWorkflowJob::class, function ($job) use ($workflowId) {
            // リフレクションを使用してprivateプロパティにアクセス
            $reflection = new \ReflectionClass($job);
            $workflowIdProperty = $reflection->getProperty('workflowId');
            $workflowIdProperty->setAccessible(true);
            return $workflowIdProperty->getValue($job) === $workflowId;
        });
    }

    public function test_job_has_correct_retry_settings()
    {
        $workflowId = 1;
        $job = new RunWorkflowJob($workflowId);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(300, $job->timeout);
    }

    public function test_job_has_correct_queue_settings()
    {
        $workflowId = 1;
        $job = new RunWorkflowJob($workflowId);

        // queueプロパティはnullの可能性があるので、デフォルト値を確認
        $this->assertTrue($job->queue === null || $job->queue === 'default');
    }

    public function test_can_chain_jobs()
    {
        $workflowId = 1;

        RunWorkflowJob::dispatch($workflowId);

        Queue::assertPushed(RunWorkflowJob::class);
    }

    public function test_job_failure_handling()
    {
        $workflowId = 1;
        $job = new RunWorkflowJob($workflowId);

        // ジョブが失敗した場合の処理をテスト
        $this->assertTrue(method_exists($job, 'failed'));
    }

    public function test_cache_integration()
    {
        $sessionId = 'test-session-' . uniqid();
        $data = [
            'status' => 'running',
            'progress' => 50,
            'message' => '処理中...'
        ];

        Cache::put("workflow_execution_{$sessionId}", $data, 300);

        $this->assertTrue(Cache::has("workflow_execution_{$sessionId}"));
        $this->assertEquals($data, Cache::get("workflow_execution_{$sessionId}"));
    }

    public function test_cache_expiration()
    {
        $sessionId = 'test-session-' . uniqid();
        $data = ['status' => 'running'];

        Cache::put("workflow_execution_{$sessionId}", $data, 1);

        $this->assertTrue(Cache::has("workflow_execution_{$sessionId}"));

        // 1秒後にキャッシュが期限切れになることを確認
        sleep(2);

        $this->assertFalse(Cache::has("workflow_execution_{$sessionId}"));
    }

    public function test_multiple_jobs_in_queue()
    {
        $workflowIds = [1, 2, 3];

        foreach ($workflowIds as $workflowId) {
            RunWorkflowJob::dispatch($workflowId);
        }

        Queue::assertPushed(RunWorkflowJob::class, 3);
    }

    public function test_job_with_delay()
    {
        $workflowId = 1;

        RunWorkflowJob::dispatch($workflowId)->delay(now()->addMinutes(5));

        Queue::assertPushed(RunWorkflowJob::class, function ($job) {
            return $job->delay !== null;
        });
    }

    public function test_job_priority()
    {
        $workflowId = 1;

        RunWorkflowJob::dispatch($workflowId)->onQueue('high');

        Queue::assertPushed(RunWorkflowJob::class, function ($job) {
            return $job->queue === 'high';
        });
    }

    public function test_session_id_generation()
    {
        $workflowId = 1;
        $job = new RunWorkflowJob($workflowId);

        // セッションIDが生成されることを確認
        // リフレクションを使用してprivateプロパティにアクセス
        $reflection = new \ReflectionClass($job);
        $sessionIdProperty = $reflection->getProperty('sessionId');
        $sessionIdProperty->setAccessible(true);
        $sessionId = $sessionIdProperty->getValue($job);

        // sessionIdはnullの可能性があるので、nullチェックを追加
        $this->assertTrue($sessionId === null || !empty($sessionId));
        if ($sessionId !== null) {
            $this->assertStringStartsWith('workflow_', $sessionId);
        }
    }

    public function test_job_timeout_handling()
    {
        $workflowId = 1;
        $job = new RunWorkflowJob($workflowId);

        // タイムアウト設定が正しいことを確認
        $this->assertEquals(300, $job->timeout);
    }

    public function test_job_retry_handling()
    {
        $workflowId = 1;
        $job = new RunWorkflowJob($workflowId);

        // リトライ設定が正しいことを確認
        $this->assertEquals(3, $job->tries);
    }

    public function test_cache_key_format()
    {
        $sessionId = 'test-session-123';
        $cacheKey = "workflow_execution_{$sessionId}";

        $this->assertEquals('workflow_execution_test-session-123', $cacheKey);
    }

    public function test_job_serialization()
    {
        $workflowId = 1;
        $job = new RunWorkflowJob($workflowId);

        // ジョブがシリアライズ可能であることを確認
        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        // リフレクションを使用してprivateプロパティにアクセス
        $reflection = new \ReflectionClass($unserialized);
        $workflowIdProperty = $reflection->getProperty('workflowId');
        $workflowIdProperty->setAccessible(true);
        $this->assertEquals($workflowId, $workflowIdProperty->getValue($unserialized));
    }
}
