<?php

namespace Tests\Unit;

use App\Domain\Services\FormatterNodeProcessor;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FormatterNodeProcessorTest extends TestCase
{
    private FormatterNodeProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new FormatterNodeProcessor();
    }

    #[Test]
    public function 全角文字を半角に変換できる()
    {
        $input = "１２３ＡＢＣｱｲｳ";
        $config = ['format_type' => 'halfwidth'];
        $result = $this->processor->process($config, $input);

        $this->assertEquals("123ABCｱｲｳ", $result);
    }

    #[Test]
    public function 半角文字を全角に変換できる()
    {
        $input = "123ABCアイウ";
        $config = ['format_type' => 'fullwidth'];
        $result = $this->processor->process($config, $input);

        $this->assertEquals("１２３ＡＢＣアイウ", $result);
    }

    #[Test]
    public function 大文字に変換できる()
    {
        $input = "hello world";
        $config = ['format_type' => 'uppercase'];
        $result = $this->processor->process($config, $input);

        $this->assertEquals("HELLO WORLD", $result);
    }

    #[Test]
    public function 小文字に変換できる()
    {
        $input = "HELLO WORLD";
        $config = ['format_type' => 'lowercase'];
        $result = $this->processor->process($config, $input);

        $this->assertEquals("hello world", $result);
    }

    #[Test]
    public function フォーマットタイプが指定されていない場合は元のテキストを返す()
    {
        $input = "test text";
        $config = [];
        $result = $this->processor->process($config, $input);

        $this->assertEquals("test text", $result);
    }

    #[Test]
    public function 未知のフォーマットタイプの場合は元のテキストを返す()
    {
        $input = "test text";
        $config = ['format_type' => 'unknown'];
        $result = $this->processor->process($config, $input);

        $this->assertEquals("test text", $result);
    }

    #[Test]
    public function 入力がnullの場合は例外を投げる()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('入力データが必要です');

        $config = ['format_type' => 'uppercase'];
        $this->processor->process($config, null);
    }

    #[Test]
    public function mb_convert_kanaの各オプションをテスト()
    {
        $text = "１２３ＡＢＣｱｲｳ";

        // 各オプションをテスト
        $options = [
            'r' => '全角英数字を半角英数字に変換',
            'R' => '半角英数字を全角英数字に変換',
            'n' => '全角数字を半角数字に変換',
            'N' => '半角数字を全角数字に変換',
            'a' => '全角英数字を半角英数字に変換（n + r）',
            'A' => '半角英数字を全角英数字に変換（N + R）',
        ];

        foreach ($options as $option => $description) {
            $result = mb_convert_kana($text, $option);
            $this->assertIsString($result, "オプション '{$option}' ({$description}) で文字列が返されることを確認");
        }
    }
}
