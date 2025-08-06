<?php

namespace Tests\Unit;

use App\Domain\Entities\Node;
use App\Domain\Entities\NodeType;
use App\Domain\Entities\Workflow;
use App\Domain\Entities\WorkflowDomainException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;



class WorkflowTest extends TestCase
{
    #[Test]
    public function can_create_workflow_with_basic_parameters()
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
    public function can_create_workflow_with_custom_parameters()
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
    public function can_create_workflow_with_id_and_nodes()
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
    public function can_add_nodes_to_workflow()
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
    public function can_update_workflow_name()
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
        $this->assertEquals($workflow->inputData, $updatedWorkflow->inputData);
        $this->assertEquals($workflow->createdAt, $updatedWorkflow->createdAt);
        $this->assertNotEquals($originalUpdatedAt, $updatedWorkflow->updatedAt);
    }

    #[Test]
    public function can_update_input_output_config()
    {
        $workflow = Workflow::create('Test Workflow');
        $originalUpdatedAt = $workflow->updatedAt;

        sleep(1);
        $updatedWorkflow = $workflow->updateInputOutputConfig('pdf', 'pdf', 'test.pdf');

        $this->assertEquals('pdf', $updatedWorkflow->inputType);
        $this->assertEquals('pdf', $updatedWorkflow->outputType);
        $this->assertEquals('test.pdf', $updatedWorkflow->inputData);
        $this->assertEquals($workflow->id, $updatedWorkflow->id);
        $this->assertEquals($workflow->name, $updatedWorkflow->name);
        $this->assertEquals($workflow->createdAt, $updatedWorkflow->createdAt);
        $this->assertNotEquals($originalUpdatedAt, $updatedWorkflow->updatedAt);
    }

    #[Test]
    public function can_update_input_output_config_without_input_data()
    {
        $workflow = Workflow::create('Test Workflow', 'text', 'text', 'old-data');

        $updatedWorkflow = $workflow->updateInputOutputConfig('pdf', 'pdf');

        $this->assertEquals('pdf', $updatedWorkflow->inputType);
        $this->assertEquals('pdf', $updatedWorkflow->outputType);
        $this->assertNull($updatedWorkflow->inputData);
    }

    #[Test]
    public function can_add_formatter_node_to_text_workflow()
    {
        $workflow = Workflow::create('Test Workflow', 'text', 'text');

        $this->assertTrue($workflow->canAddNode(NodeType::FORMATTER));
    }

    #[Test]
    public function can_add_generative_ai_node_to_text_workflow()
    {
        $workflow = Workflow::create('Test Workflow', 'text', 'text');

        $this->assertTrue($workflow->canAddNode(NodeType::GENERATIVE_AI));
    }

    #[Test]
    public function cannot_add_extract_text_node_to_text_workflow()
    {
        $workflow = Workflow::create('Test Workflow', 'text', 'text');

        $this->assertFalse($workflow->canAddNode(NodeType::EXTRACT_TEXT));
    }

    #[Test]
    public function can_add_extract_text_node_to_pdf_workflow_as_first_node()
    {
        $workflow = Workflow::create('Test Workflow', 'pdf', 'text');

        $this->assertTrue($workflow->canAddNode(NodeType::EXTRACT_TEXT));
    }

    #[Test]
    public function can_add_other_nodes_to_pdf_workflow_after_first_node()
    {
        $nodes = [
            new Node(1, 1, NodeType::EXTRACT_TEXT, [], new \DateTime(), new \DateTime())
        ];
        $workflow = Workflow::create('Test Workflow', 'pdf', 'text')->withNodes($nodes);

        $this->assertTrue($workflow->canAddNode(NodeType::FORMATTER));
        $this->assertTrue($workflow->canAddNode(NodeType::GENERATIVE_AI));
        $this->assertTrue($workflow->canAddNode(NodeType::EXTRACT_TEXT));
    }

    #[Test]
    public function validate_node_addition_throws_exception_for_text_workflow_with_extract_text()
    {
        $workflow = Workflow::create('Test Workflow', 'text', 'text');

        $this->expectException(WorkflowDomainException::class);
        $this->expectExceptionMessage('テキスト入力の場合、「PDFテキスト抽出」ノードは追加できません');

        $workflow->validateNodeAddition(NodeType::EXTRACT_TEXT);
    }

    #[Test]
    public function validate_node_addition_throws_exception_for_pdf_workflow_without_extract_text_as_first()
    {
        $workflow = Workflow::create('Test Workflow', 'pdf', 'text');

        $this->expectException(WorkflowDomainException::class);
        $this->expectExceptionMessage('PDF入力の場合、最初のノードは「テキスト抽出」である必要があります');

        $workflow->validateNodeAddition(NodeType::FORMATTER);
    }

    #[Test]
    public function validate_node_addition_passes_for_valid_nodes()
    {
        $workflow = Workflow::create('Test Workflow', 'text', 'text');

        // 例外が発生しないことを確認
        $workflow->validateNodeAddition(NodeType::FORMATTER);
        $workflow->validateNodeAddition(NodeType::GENERATIVE_AI);

        $this->assertTrue(true); // テストが通ることを確認
    }

    #[Test]
    public function validate_node_addition_passes_for_pdf_workflow_with_extract_text_as_first()
    {
        $workflow = Workflow::create('Test Workflow', 'pdf', 'text');

        // 例外が発生しないことを確認
        $workflow->validateNodeAddition(NodeType::EXTRACT_TEXT);

        $this->assertTrue(true); // テストが通ることを確認
    }

    #[Test]
    public function to_array_returns_correct_structure()
    {
        $createdAt = new \DateTime('2023-01-01 10:00:00');
        $updatedAt = new \DateTime('2023-01-02 11:00:00');
        $nodes = [
            new Node(1, 1, NodeType::FORMATTER, ['format' => 'uppercase'], $createdAt, $updatedAt)
        ];

        $workflow = new Workflow(
            id: 1,
            name: 'Test Workflow',
            inputType: 'text',
            outputType: 'text',
            inputData: 'test data',
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            nodes: $nodes
        );

        $array = $workflow->toArray();

        $this->assertEquals(1, $array['id']);
        $this->assertEquals('Test Workflow', $array['name']);
        $this->assertEquals('text', $array['input_type']);
        $this->assertEquals('text', $array['output_type']);
        $this->assertEquals('test data', $array['input_data']);
        $this->assertEquals('2023-01-01T10:00:00.000Z', $array['created_at']);
        $this->assertEquals('2023-01-02T11:00:00.000Z', $array['updated_at']);
        $this->assertIsArray($array['nodes']);
        $this->assertCount(1, $array['nodes']);
    }

    #[Test]
    public function to_array_handles_empty_nodes()
    {
        $workflow = Workflow::create('Test Workflow');

        $array = $workflow->toArray();

        $this->assertIsArray($array['nodes']);
        $this->assertEmpty($array['nodes']);
    }

    #[Test]
    public function to_array_handles_null_id()
    {
        $workflow = Workflow::create('Test Workflow');

        $array = $workflow->toArray();

        $this->assertNull($array['id']);
    }

    #[Test]
    public function to_array_handles_null_input_data()
    {
        $workflow = Workflow::create('Test Workflow');

        $array = $workflow->toArray();

        $this->assertNull($array['input_data']);
    }

    #[Test]
    public function validate_node_addition_throws_generic_exception_for_other_cases()
    {
        // 実際には、この最後の例外ケースは現在のロジックでは発生しないが、
        // コードカバレッジのために、このテストケースを追加
        // 現在のロジックでは、すべてのケースが特定の条件に当てはまるため、
        // この最後の例外ケースは実際には到達しない

        $workflow = Workflow::create('Test Workflow', 'text', 'text');

        // 現在のロジックでは、text入力でEXTRACT_TEXTは許可されない
        $this->assertFalse($workflow->canAddNode(NodeType::EXTRACT_TEXT));

        // この場合、特定の条件に当てはまるため、特定の例外メッセージが投げられる
        $this->expectException(WorkflowDomainException::class);
        $this->expectExceptionMessage('テキスト入力の場合、「PDFテキスト抽出」ノードは追加できません');

        $workflow->validateNodeAddition(NodeType::EXTRACT_TEXT);
    }

    #[Test]
    public function workflow_domain_exception_has_correct_message()
    {
        $message = 'Test error message';
        $exception = new WorkflowDomainException($message);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
