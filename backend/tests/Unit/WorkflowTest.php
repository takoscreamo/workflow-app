<?php

namespace Tests\Unit;

use App\Domain\Entities\Node;
use App\Domain\Entities\NodeType;
use App\Domain\Entities\Workflow;
use App\Domain\Entities\WorkflowDomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WorkflowTest extends TestCase
{
    #[Test]
    public function 基本的なパラメータでワークフローを作成できる()
    {
        $workflow = Workflow::create('Test Workflow');

        $this->assertNull($workflow->id);
        $this->assertEquals('Test Workflow', $workflow->name);
        $this->assertEquals('text', $workflow->inputType);
        $this->assertEquals('text', $workflow->outputType);
        $this->assertNull($workflow->inputData);
        $this->assertEmpty($workflow->nodes);
        $this->assertInstanceOf(\DateTime::class, $workflow->createdAt);
        $this->assertInstanceOf(\DateTime::class, $workflow->updatedAt);
    }

    #[Test]
    public function カスタムパラメータでワークフローを作成できる()
    {
        $workflow = Workflow::create(
            name: 'PDF Workflow',
            inputType: 'pdf',
            outputType: 'pdf',
            inputData: 'test.pdf'
        );

        $this->assertEquals('PDF Workflow', $workflow->name);
        $this->assertEquals('pdf', $workflow->inputType);
        $this->assertEquals('pdf', $workflow->outputType);
        $this->assertEquals('test.pdf', $workflow->inputData);
    }

    #[Test]
    public function IDとノードでワークフローを作成できる()
    {
        $nodes = [
            new Node(1, 1, NodeType::FORMATTER, ['format' => 'uppercase'], new \DateTime(), new \DateTime())
        ];

        $workflow = new Workflow(
            id: 1,
            name: 'Test Workflow',
            inputType: 'text',
            outputType: 'text',
            inputData: null,
            createdAt: new \DateTime('2023-01-01'),
            updatedAt: new \DateTime('2023-01-02'),
            nodes: $nodes
        );

        $this->assertEquals(1, $workflow->id);
        $this->assertCount(1, $workflow->nodes);
        $this->assertEquals('2023-01-01', $workflow->createdAt->format('Y-m-d'));
        $this->assertEquals('2023-01-02', $workflow->updatedAt->format('Y-m-d'));
    }

    #[Test]
    public function ワークフローにノードを追加できる()
    {
        $workflow = Workflow::create('Test Workflow');
        $nodes = [
            new Node(1, 1, NodeType::FORMATTER, ['format' => 'uppercase'], new \DateTime(), new \DateTime())
        ];

        $workflowWithNodes = $workflow->withNodes($nodes);

        $this->assertCount(1, $workflowWithNodes->nodes);
        $this->assertSame($nodes, $workflowWithNodes->nodes);
        $this->assertEquals($workflow->id, $workflowWithNodes->id);
        $this->assertEquals($workflow->name, $workflowWithNodes->name);
    }

    #[Test]
    public function ワークフロー名を更新できる()
    {
        $workflow = Workflow::create('Old Name');
        $originalUpdatedAt = $workflow->updatedAt;

        // 少し待ってから更新
        sleep(1);
        $updatedWorkflow = $workflow->updateName('New Name');

        $this->assertEquals('New Name', $updatedWorkflow->name);
        $this->assertEquals($workflow->id, $updatedWorkflow->id);
        $this->assertEquals($workflow->inputType, $updatedWorkflow->inputType);
        $this->assertEquals($workflow->outputType, $updatedWorkflow->outputType);
        $this->assertGreaterThan($originalUpdatedAt, $updatedWorkflow->updatedAt);
    }

    #[Test]
    public function 入出力設定を更新できる()
    {
        $workflow = Workflow::create('Test Workflow');
        $originalUpdatedAt = $workflow->updatedAt;

        sleep(1);
        $updatedWorkflow = $workflow->updateInputOutputConfig('pdf', 'text', 'test.pdf');

        $this->assertEquals('pdf', $updatedWorkflow->inputType);
        $this->assertEquals('text', $updatedWorkflow->outputType);
        $this->assertEquals('test.pdf', $updatedWorkflow->inputData);
        $this->assertGreaterThan($originalUpdatedAt, $updatedWorkflow->updatedAt);
    }

    #[Test]
    public function 入力データなしで入出力設定を更新できる()
    {
        $workflow = Workflow::create('Test Workflow');
        $originalUpdatedAt = $workflow->updatedAt;

        sleep(1);
        $updatedWorkflow = $workflow->updateInputOutputConfig('pdf', 'text');

        $this->assertEquals('pdf', $updatedWorkflow->inputType);
        $this->assertEquals('text', $updatedWorkflow->outputType);
        $this->assertNull($updatedWorkflow->inputData);
        $this->assertGreaterThan($originalUpdatedAt, $updatedWorkflow->updatedAt);
    }

    #[Test]
    public function テキストワークフローにフォーマッターノードを追加できる()
    {
        $workflow = Workflow::create('Test Workflow');
        $this->assertTrue($workflow->canAddNode(NodeType::FORMATTER));
    }

    #[Test]
    public function テキストワークフローに生成AIノードを追加できる()
    {
        $workflow = Workflow::create('Test Workflow');
        $this->assertTrue($workflow->canAddNode(NodeType::GENERATIVE_AI));
    }

    #[Test]
    public function テキストワークフローにテキスト抽出ノードを追加できない()
    {
        $workflow = Workflow::create('Test Workflow');
        $this->assertFalse($workflow->canAddNode(NodeType::EXTRACT_TEXT));
    }

    #[Test]
    public function PDFワークフローの最初のノードとしてテキスト抽出ノードを追加できる()
    {
        $workflow = Workflow::create('PDF Workflow', 'pdf', 'text');
        $this->assertTrue($workflow->canAddNode(NodeType::EXTRACT_TEXT));
    }

    #[Test]
    public function PDFワークフローの最初のノード後に他のノードを追加できる()
    {
        $workflow = Workflow::create('PDF Workflow', 'pdf', 'text');
        $nodes = [
            new Node(1, 1, NodeType::EXTRACT_TEXT, ['file_path' => 'test.pdf'], new \DateTime(), new \DateTime())
        ];
        $workflowWithNodes = $workflow->withNodes($nodes);

        $this->assertTrue($workflowWithNodes->canAddNode(NodeType::FORMATTER));
        $this->assertTrue($workflowWithNodes->canAddNode(NodeType::GENERATIVE_AI));
    }

    #[Test]
    public function テキストワークフローにテキスト抽出ノードを追加しようとすると例外が発生する()
    {
        $this->expectException(WorkflowDomainException::class);
        $this->expectExceptionMessage('テキスト入力の場合、「PDFテキスト抽出」ノードは追加できません');

        $workflow = Workflow::create('Test Workflow');
        $workflow->validateNodeAddition(NodeType::EXTRACT_TEXT);
    }

    #[Test]
    public function PDFワークフローの最初のノードとしてテキスト抽出以外を追加しようとすると例外が発生する()
    {
        $this->expectException(WorkflowDomainException::class);
        $this->expectExceptionMessage('PDF入力の場合、最初のノードは「テキスト抽出」である必要があります');

        $workflow = Workflow::create('PDF Workflow', 'pdf', 'text');
        $workflow->validateNodeAddition(NodeType::FORMATTER);
    }

    #[Test]
    public function 有効なノードの追加検証が通る()
    {
        $workflow = Workflow::create('Test Workflow');

        // 例外が発生しないことを確認
        $this->expectNotToPerformAssertions();
        $workflow->validateNodeAddition(NodeType::FORMATTER);
    }

    #[Test]
    public function PDFワークフローの最初のノードとしてテキスト抽出を追加する検証が通る()
    {
        $workflow = Workflow::create('PDF Workflow', 'pdf', 'text');

        // 例外が発生しないことを確認
        $this->expectNotToPerformAssertions();
        $workflow->validateNodeAddition(NodeType::EXTRACT_TEXT);
    }

    #[Test]
    public function toArrayが正しい構造を返す()
    {
        $nodes = [
            new Node(1, 1, NodeType::FORMATTER, ['format' => 'uppercase'], new \DateTime('2023-01-01'), new \DateTime('2023-01-02'))
        ];

        $workflow = new Workflow(
            id: 1,
            name: 'Test Workflow',
            inputType: 'text',
            outputType: 'text',
            inputData: 'test input',
            createdAt: new \DateTime('2023-01-01'),
            updatedAt: new \DateTime('2023-01-02'),
            nodes: $nodes
        );

        $array = $workflow->toArray();

        $this->assertEquals(1, $array['id']);
        $this->assertEquals('Test Workflow', $array['name']);
        $this->assertEquals('text', $array['input_type']);
        $this->assertEquals('text', $array['output_type']);
        $this->assertEquals('test input', $array['input_data']);
        $this->assertEquals('2023-01-01T00:00:00.000Z', $array['created_at']);
        $this->assertEquals('2023-01-02T00:00:00.000Z', $array['updated_at']);
        $this->assertCount(1, $array['nodes']);
        $this->assertEquals(1, $array['nodes'][0]['id']);
        $this->assertEquals(1, $array['nodes'][0]['workflow_id']);
        $this->assertEquals('formatter', $array['nodes'][0]['node_type']);
        $this->assertEquals(['format' => 'uppercase'], $array['nodes'][0]['config']);
    }

    #[Test]
    public function toArrayが空のノードを処理できる()
    {
        $workflow = new Workflow(
            id: 1,
            name: 'Test Workflow',
            inputType: 'text',
            outputType: 'text',
            inputData: null,
            createdAt: new \DateTime('2023-01-01'),
            updatedAt: new \DateTime('2023-01-02'),
            nodes: []
        );

        $array = $workflow->toArray();

        $this->assertEquals(1, $array['id']);
        $this->assertEquals('Test Workflow', $array['name']);
        $this->assertCount(0, $array['nodes']);
    }

    #[Test]
    public function toArrayがnullのIDを処理できる()
    {
        $workflow = new Workflow(
            id: null,
            name: 'Test Workflow',
            inputType: 'text',
            outputType: 'text',
            inputData: null,
            createdAt: new \DateTime('2023-01-01'),
            updatedAt: new \DateTime('2023-01-02'),
            nodes: []
        );

        $array = $workflow->toArray();

        $this->assertNull($array['id']);
        $this->assertEquals('Test Workflow', $array['name']);
    }

    #[Test]
    public function toArrayがnullの入力データを処理できる()
    {
        $workflow = new Workflow(
            id: 1,
            name: 'Test Workflow',
            inputType: 'text',
            outputType: 'text',
            inputData: null,
            createdAt: new \DateTime('2023-01-01'),
            updatedAt: new \DateTime('2023-01-02'),
            nodes: []
        );

        $array = $workflow->toArray();

        $this->assertEquals(1, $array['id']);
        $this->assertNull($array['input_data']);
    }



    #[Test]
    public function ワークフロードメイン例外が正しいメッセージを持つ()
    {
        $exception = new WorkflowDomainException('テストエラーメッセージ');

        $this->assertEquals('テストエラーメッセージ', $exception->getMessage());
    }
}
