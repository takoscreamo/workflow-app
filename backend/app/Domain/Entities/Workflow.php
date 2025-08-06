<?php

namespace App\Domain\Entities;

use App\Domain\Entities\Node;
use Illuminate\Database\Eloquent\Collection;

/**
 * ワークフローのドメインルール違反を表す例外
 */
class WorkflowDomainException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}

class Workflow
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $inputType,
        public readonly string $outputType,
        public readonly ?string $inputData,
        public readonly \DateTime $createdAt,
        public readonly \DateTime $updatedAt,
        public readonly Collection $nodes = new Collection()
    ) {}

    public static function create(string $name, string $inputType = 'text', string $outputType = 'text', ?string $inputData = null): self
    {
        return new self(
            id: null,
            name: $name,
            inputType: $inputType,
            outputType: $outputType,
            inputData: $inputData,
            createdAt: new \DateTime(),
            updatedAt: new \DateTime()
        );
    }

    public function withNodes(Collection $nodes): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            inputType: $this->inputType,
            outputType: $this->outputType,
            inputData: $this->inputData,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
            nodes: $nodes
        );
    }

    public function updateName(string $name): self
    {
        return new self(
            id: $this->id,
            name: $name,
            inputType: $this->inputType,
            outputType: $this->outputType,
            inputData: $this->inputData,
            createdAt: $this->createdAt,
            updatedAt: new \DateTime(),
            nodes: $this->nodes
        );
    }

    public function updateInputOutputConfig(string $inputType, string $outputType, ?string $inputData = null): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            inputType: $inputType,
            outputType: $outputType,
            inputData: $inputData,
            createdAt: $this->createdAt,
            updatedAt: new \DateTime(),
            nodes: $this->nodes
        );
    }

    /**
     * ノードを追加できるかどうかを検証するドメインルール
     * PDF入力の場合、最初のノードはEXTRACT_TEXTである必要がある
     * テキスト入力の場合、EXTRACT_TEXTノードは追加できない
     */
    public function canAddNode(NodeType $nodeType): bool
    {
        // PDF入力で最初のノードの場合、EXTRACT_TEXTのみ許可
        if ($this->inputType === 'pdf' && $this->nodes->isEmpty()) {
            return $nodeType === NodeType::EXTRACT_TEXT;
        }

        // テキスト入力の場合、EXTRACT_TEXTは許可しない
        if ($this->inputType === 'text' && $nodeType === NodeType::EXTRACT_TEXT) {
            return false;
        }

        return true;
    }

    /**
     * ノード追加時のドメインルール違反をチェック
     * @throws WorkflowDomainException
     */
    public function validateNodeAddition(NodeType $nodeType): void
    {
        if (!$this->canAddNode($nodeType)) {
            if ($this->inputType === 'pdf' && $this->nodes->isEmpty()) {
                throw new WorkflowDomainException('PDF入力の場合、最初のノードは「テキスト抽出」である必要があります');
            }
            if ($this->inputType === 'text' && $nodeType === NodeType::EXTRACT_TEXT) {
                throw new WorkflowDomainException('テキスト入力の場合、「PDFテキスト抽出」ノードは追加できません');
            }
            throw new WorkflowDomainException('このノードタイプは追加できません');
        }
    }
}
