<?php

namespace App\Domain\Services;

class GenerativeAiNodeProcessor implements NodeProcessorInterface
{
    public function process(array $config, ?string $input = null): string
    {
        $apiKey = config('services.openrouter.api_key');

        if (!$apiKey) {
            throw new \Exception('OpenRouter APIキーが設定されていません');
        }

        $prompt = $config['prompt'] ?? '以下のテキストを処理してください：';
        $model = $config['model'] ?? 'google/gemma-3n-e2b-it:free';
        $maxTokens = $config['max_tokens'] ?? 1000;
        $temperature = $config['temperature'] ?? 0.7;

        // 入力テキストがある場合は、プロンプトに追加
        $fullPrompt = $input ? $prompt . "\n\n" . $input : $prompt;

        try {
            $response = $this->callOpenRouterApi($apiKey, $model, $fullPrompt, $maxTokens, $temperature);
            return $response ?: 'AIからの応答が空でした';
        } catch (\Exception $e) {
            throw new \Exception("OpenRouter API呼び出しに失敗しました: " . $e->getMessage());
        }
    }

    private function callOpenRouterApi(string $apiKey, string $model, string $prompt, int $maxTokens, float $temperature): string
    {
        $url = 'https://openrouter.ai/api/v1/chat/completions';

        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ];

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: https://github.com/your-app',
            'X-Title: Workflow App',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL error: " . $error);
        }

        if ($httpCode !== 200) {
            throw new \Exception("HTTP error: " . $httpCode . ", Response: " . $response);
        }

        $responseData = json_decode($response, true);

        if (!$responseData || !isset($responseData['choices'][0]['message']['content'])) {
            throw new \Exception("Invalid response format: " . $response);
        }

        return $responseData['choices'][0]['message']['content'];
    }
}
