<?php

namespace App\Domain\Services;

use Spatie\PdfToText\Pdf;

class ExtractTextNodeProcessor implements NodeProcessorInterface
{
    public function process(array $config, ?string $input = null): string
    {
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
            $text = (new Pdf())
                ->setPdf($fullPath)
                ->text();

            return $text ?: 'テキストが抽出できませんでした';
        } catch (\Exception $e) {
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
