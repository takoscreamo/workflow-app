<?php

namespace App\Domain\Services;

interface NodeProcessorInterface
{
    /**
     * ノードを実行する
     *
     * @param array $config ノードの設定
     * @param string|null $input 入力データ
     * @return string 処理結果
     */
    public function process(array $config, ?string $input = null): string;
}
