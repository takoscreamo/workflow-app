<?php

namespace App\Domain\Services;

use Spatie\PdfToText\Pdf;

class ExtractTextNodeProcessor implements NodeProcessorInterface
{
    public function process(array $config, ?string $input = null): string
    {
        $filePath = $config['file_path'] ?? null;

        if (!$filePath) {
            throw new \InvalidArgumentException('ファイルパスが指定されていません');
        }

        $fullPath = storage_path('app/public/' . $filePath);

        if (!file_exists($fullPath)) {
            throw new \Exception("ファイルが見つかりません: {$filePath}");
        }

        try {
            $text = (new Pdf())
                ->setPdf($fullPath)
                ->text();

            return $text ?: 'テキストが抽出できませんでした';
        } catch (\Exception $e) {
            throw new \Exception("PDFからテキスト抽出に失敗しました: " . $e->getMessage());
        }
    }
}
