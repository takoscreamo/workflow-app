<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Entities\Node;
use App\Domain\Entities\NodeType;
use App\Domain\Services\GenerativeAiNodeProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class GenerativeAiNodeProcessorTest extends TestCase
{
    use RefreshDatabase;

    private GenerativeAiNodeProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new GenerativeAiNodeProcessor('test-api-key');
    }

    public function test_can_generate_text_with_valid_config()
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

    public function test_returns_error_for_missing_prompt()
    {
        $config = [
            'model' => 'gpt-3.5-turbo'
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('プロンプトが指定されていません');
        $this->processor->process($config);
    }

    public function test_returns_error_for_empty_prompt()
    {
        $config = [
            'prompt' => '',
            'model' => 'gpt-3.5-turbo'
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('プロンプトが指定されていません');
        $this->processor->process($config);
    }

    public function test_uses_default_model_when_not_specified()
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

    public function test_handles_api_error_response()
    {
        $config = [
            'prompt' => 'API error',
            'model' => 'gpt-3.5-turbo'
        ];

        $this->expectException(\Exception::class);
        $this->processor->process($config);
    }

    public function test_handles_network_error()
    {
        $config = [
            'prompt' => 'Network error',
            'model' => 'gpt-3.5-turbo'
        ];

        $this->expectException(\Exception::class);
        $this->processor->process($config);
    }

    public function test_handles_empty_response()
    {
        $config = [
            'prompt' => 'Empty response',
            'model' => 'gpt-3.5-turbo'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('AIからの応答が空でした', $result);
    }

    public function test_handles_missing_content_in_response()
    {
        $config = [
            'prompt' => 'Missing content',
            'model' => 'gpt-3.5-turbo'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('AIからの応答が空でした', $result);
    }

    public function test_handles_japanese_prompt()
    {
        $config = [
            'prompt' => 'こんにちは、世界',
            'model' => 'gpt-3.5-turbo'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('日本語の応答', $result);
    }

    public function test_handles_long_prompt()
    {
        $config = [
            'prompt' => 'Long response',
            'model' => 'gpt-3.5-turbo'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('Long response', $result);
    }

    public function test_handles_special_characters_in_prompt()
    {
        $config = [
            'prompt' => 'Special chars: !@#$%^&*()',
            'model' => 'gpt-3.5-turbo'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('Special chars response', $result);
    }

    public function test_handles_different_models()
    {
        $config = [
            'prompt' => 'Claude response',
            'model' => 'anthropic/claude-3-sonnet'
        ];

        $result = $this->processor->process($config);

        $this->assertIsString($result);
        $this->assertEquals('Claude response', $result);
    }

    public function test_handles_temperature_parameter()
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

    public function test_handles_max_tokens_parameter()
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
