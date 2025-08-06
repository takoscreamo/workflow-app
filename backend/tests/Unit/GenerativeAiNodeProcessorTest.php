<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Entities\Node;
use App\Domain\Entities\NodeType;
use App\Domain\Services\GenerativeAiNodeProcessor;
use Illuminate\Support\Facades\Http;

class GenerativeAiNodeProcessorTest extends TestCase
{
    private GenerativeAiNodeProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new GenerativeAiNodeProcessor('test-api-key');
    }

    public function test_有効な設定でテキストを生成できる()
    {
        Http::fake([
            'api.openrouter.ai/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Generated response'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $config = [
            'prompt' => 'テストプロンプト',
            'model' => 'gpt-3.5-turbo',
            'temperature' => 0.7,
            'max_tokens' => 100
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('Generated response', $result);
    }

    public function test_プロンプトが不足している場合はエラーを返す()
    {
        $config = [
            'model' => 'gpt-3.5-turbo'
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('プロンプトが指定されていません');
        $this->processor->process($config);
    }

    public function test_空のプロンプトの場合はエラーを返す()
    {
        $config = [
            'prompt' => '',
            'model' => 'gpt-3.5-turbo'
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('プロンプトが指定されていません');
        $this->processor->process($config);
    }

    public function test_モデルが指定されていない場合はデフォルトモデルを使用する()
    {
        Http::fake([
            'api.openrouter.ai/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Default model response'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $config = [
            'prompt' => 'Default model response'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('Default model response', $result);
    }

    public function test_APIエラーレスポンスを処理できる()
    {
        $config = [
            'prompt' => 'API error',
            'model' => 'gpt-3.5-turbo'
        ];

        $this->expectException(\Exception::class);
        $this->processor->process($config);
    }

    public function test_ネットワークエラーを処理できる()
    {
        $config = [
            'prompt' => 'Network error',
            'model' => 'gpt-3.5-turbo'
        ];

        $this->expectException(\Exception::class);
        $this->processor->process($config);
    }

    public function test_空のレスポンスを処理できる()
    {
        $config = [
            'prompt' => 'Empty response',
            'model' => 'gpt-3.5-turbo'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('AIからの応答が空でした', $result);
    }

    public function test_レスポンスにコンテンツが不足している場合を処理できる()
    {
        $config = [
            'prompt' => 'Missing content',
            'model' => 'gpt-3.5-turbo'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('AIからの応答が空でした', $result);
    }

    public function test_日本語プロンプトを処理できる()
    {
        $config = [
            'prompt' => 'こんにちは、世界',
            'model' => 'gpt-3.5-turbo'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('日本語の応答', $result);
    }

    public function test_長いプロンプトを処理できる()
    {
        $config = [
            'prompt' => 'Long response',
            'model' => 'gpt-3.5-turbo'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('Long response', $result);
    }

    public function test_プロンプト内の特殊文字を処理できる()
    {
        $config = [
            'prompt' => 'Special chars: !@#$%^&*()',
            'model' => 'gpt-3.5-turbo'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('Special chars response', $result);
    }

    public function test_異なるモデルを処理できる()
    {
        $config = [
            'prompt' => 'Claude response',
            'model' => 'anthropic/claude-3-sonnet'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('Claude response', $result);
    }

    public function test_温度パラメータを処理できる()
    {
        $config = [
            'prompt' => 'Temperature response',
            'model' => 'gpt-3.5-turbo',
            'temperature' => 0.9
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('Temperature response', $result);
    }

    public function test_最大トークンパラメータを処理できる()
    {
        $config = [
            'prompt' => 'Max tokens response',
            'model' => 'gpt-3.5-turbo',
            'max_tokens' => 500
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);

        $this->assertEquals('Max tokens response', $result);
    }
}
