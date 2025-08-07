<?php

namespace App\Domain\Services;

use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\Log;

class ExtractTextNodeProcessor implements NodeProcessorInterface
{
    public function process(array $config, ?string $input = null): string
    {
        Log::info("ExtractTextNodeProcessor.process開始", [
            'config' => $config,
            'input' => $input
        ]);

        // 入力がPDFファイルパスの場合、それを直接使用
        if ($input && $this->isPdfFilePath($input)) {
            $filePath = $input;
        } else {
            // 従来の設定からファイルパスを取得
            $filePath = $config['file_path'] ?? null;
        }

        if (!$filePath) {
            throw new \InvalidArgumentException('PDFファイルパスが指定されていません');
        }

        $fullPath = storage_path('app/public/' . $filePath);

        if (!file_exists($fullPath)) {
            throw new \Exception("ファイルが見つかりません: {$filePath}");
        }

        try {
            Log::info("PDFテキスト抽出開始", [
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath)
            ]);

            $text = (new Pdf())
                ->setPdf($fullPath)
                ->text();

            Log::info("PDFテキスト抽出完了", [
                'text_length' => strlen($text),
                'text_preview' => substr($text, 0, 100)
            ]);

            return $text ?: 'テキストが抽出できませんでした';
        } catch (\Exception $e) {
            Log::error("PDFテキスト抽出エラー", [
                'full_path' => $fullPath,
                'error' => $e->getMessage()
            ]);
            throw new \Exception("PDFからテキスト抽出に失敗しました: " . $e->getMessage());
        }
    }

    /**
     * 入力がPDFファイルパスかどうかを判定
     */
    private function isPdfFilePath(?string $input): bool
    {
        if (!$input) {
            return false;
        }

        // uploads/で始まり、.pdfで終わるパスかチェック
        return preg_match('/^uploads\/.*\.pdf$/i', $input);
    }
}
