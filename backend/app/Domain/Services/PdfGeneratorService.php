<?php

namespace App\Domain\Services;

use TCPDF;

class PdfGeneratorService
{
    public function generatePdfFromText(string $text): string
    {
        // TCPDFインスタンスを作成
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // ドキュメント情報を設定
        $pdf->SetCreator('Workflow App');
        $pdf->SetAuthor('Workflow App');
        $pdf->SetTitle('ワークフロー実行結果');
        $pdf->SetSubject('ワークフロー実行結果');

        // ヘッダーとフッターを無効化
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // マージンを設定
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(TRUE, 20);

        // フォントを設定（日本語対応）
        $pdf->SetFont('kozminproregular', '', 12);

        // ページを追加
        $pdf->AddPage();

        // タイトルを追加
        $pdf->SetFont('kozminproregular', 'B', 16);
        $pdf->Cell(0, 10, 'ワークフロー実行結果', 0, 1, 'L');
        $pdf->Ln(5);

        // タイムスタンプを追加
        $pdf->SetFont('kozminproregular', '', 10);
        $pdf->Cell(0, 10, '生成日時: ' . date('Y-m-d H:i:s'), 0, 1, 'L');
        $pdf->Ln(10);

        // コンテンツを追加
        $pdf->SetFont('kozminproregular', '', 12);
        $pdf->MultiCell(0, 10, $text, 0, 'L');

        // PDFの内容を取得
        $pdfContent = $pdf->Output('', 'S');

        // Base64エンコードして返す
        return base64_encode($pdfContent);
    }
}
