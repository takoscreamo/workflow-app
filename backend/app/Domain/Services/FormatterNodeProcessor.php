<?php

namespace App\Domain\Services;

class FormatterNodeProcessor implements NodeProcessorInterface
{
    public function process(array $config, ?string $input = null): string
    {
        if ($input === null) {
            throw new \InvalidArgumentException('入力データが必要です');
        }

        $formatType = $config['format_type'] ?? 'none';

        return match ($formatType) {
            'uppercase' => strtoupper($input),
            'lowercase' => strtolower($input),
            'fullwidth' => $this->convertToFullwidth($input),
            'halfwidth' => $this->convertToHalfwidth($input),
            default => $input,
        };
    }

    /**
     * 半角文字を全角文字に変換
     */
    private function convertToFullwidth(string $text): string
    {
        return mb_convert_kana($text, 'R');
    }

    /**
     * 全角文字を半角文字に変換
     */
    private function convertToHalfwidth(string $text): string
    {
        return mb_convert_kana($text, 'r');
    }
}
