<?php

namespace App\Domain\Services;

use App\Domain\Entities\NodeType;

class NodeProcessorFactory
{
    public function create(NodeType $nodeType): NodeProcessorInterface
    {
        return match ($nodeType) {
            NodeType::FORMATTER => new FormatterNodeProcessor(),
            NodeType::EXTRACT_TEXT => new ExtractTextNodeProcessor(),
            NodeType::GENERATIVE_AI => new GenerativeAiNodeProcessor(),
            default => throw new \InvalidArgumentException("未対応のノードタイプです: {$nodeType->value}"),
        };
    }
}
