<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MbConvertKanaTest extends TestCase
{
    #[Test]
    public function mb_convert_kanaの各オプションをテスト()
    {
        $text = "１２３ＡＢＣｱｲｳ";

        echo "元のテキスト: {$text}\n\n";

        // 各オプションをテスト
        $options = [
            'r' => '全角英数字を半角英数字に変換',
            'R' => '半角英数字を全角英数字に変換',
            'n' => '全角数字を半角数字に変換',
            'N' => '半角数字を全角数字に変換',
            'a' => '全角英数字を半角英数字に変換（n + r）',
            'A' => '半角英数字を全角英数字に変換（N + R）',
            's' => '全角スペースを半角スペースに変換',
            'S' => '半角スペースを全角スペースに変換',
            'k' => '全角カタカナを半角カタカナに変換',
            'K' => '半角カタカナを全角カタカナに変換',
            'h' => '全角ひらがなを半角カタカナに変換',
            'H' => '半角カタカナを全角ひらがなに変換',
            'c' => '全角ひらがなを全角カタカナに変換',
            'C' => '全角カタカナを全角ひらがなに変換',
            'V' => '濁点付きの文字を一文字に変換',
        ];

        foreach ($options as $option => $description) {
            $result = mb_convert_kana($text, $option);
            echo "オプション '{$option}' ({$description}): {$result}\n";
        }

        echo "\n=== 組み合わせテスト ===\n";
        $combinations = [
            'rV' => '全角英数字を半角英数字に変換 + 濁点処理',
            'nrV' => '全角数字を半角数字に変換 + 全角英数字を半角英数字に変換 + 濁点処理',
            'arV' => '全角英数字を半角英数字に変換 + 濁点処理',
            'nr' => '全角数字を半角数字に変換 + 全角英数字を半角英数字に変換',
            'a' => '全角英数字を半角英数字に変換（n + r）',
        ];

        foreach ($combinations as $option => $description) {
            $result = mb_convert_kana($text, $option);
            echo "オプション '{$option}' ({$description}): {$result}\n";
        }

        // テストとして、期待される結果を検証
        $this->assertTrue(true); // このテストは主に出力を確認するためのもの
    }

    #[Test]
    public function 全角数字を半角数字に変換するテスト()
    {
        $input = "１２３４５６７８９０";
        $result = mb_convert_kana($input, 'n');
        $expected = "1234567890";

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function 全角アルファベットを半角アルファベットに変換するテスト()
    {
        $input = "ＡＢＣＤＥＦＧＨＩＪＫＬＭＮＯＰＱＲＳＴＵＶＷＸＹＺ";
        $result = mb_convert_kana($input, 'r');
        $expected = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function 全角英数字を半角英数字に変換するテスト()
    {
        $input = "１２３ＡＢＣ";
        $result = mb_convert_kana($input, 'a');
        $expected = "123ABC";

        $this->assertEquals($expected, $result);
    }
}
