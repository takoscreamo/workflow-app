<?php

namespace App\Domain\Services;

use OpenAI\Client;
use OpenAI\Factory;

class GenerativeAiNodeProcessor implements NodeProcessorInterface
{
    private ?Client $client = null;

    public function process(array $config, ?string $input = null): string
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            throw new \Exception('OpenAI APIキーが設定されていません');
        }

        $prompt = $config['prompt'] ?? '以下のテキストを処理してください：';
        $model = $config['model'] ?? 'gpt-3.5-turbo';
        $maxTokens = $config['max_tokens'] ?? 1000;
        $temperature = $config['temperature'] ?? 0.7;

        // 入力テキストがある場合は、プロンプトに追加
        $fullPrompt = $input ? $prompt . "\n\n" . $input : $prompt;

        try {
            $client = $this->getClient($apiKey);

            $response = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $fullPrompt,
                    ],
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

            $result = $response->choices[0]->message->content;

            return $result ?: 'AIからの応答が空でした';
        } catch (\Exception $e) {
            throw new \Exception("OpenAI API呼び出しに失敗しました: " . $e->getMessage());
        }
    }

    private function getClient(string $apiKey): Client
    {
        if ($this->client === null) {
            $this->client = (new Factory())
                ->withApiKey($apiKey)
                ->make();
        }

        return $this->client;
    }
}
