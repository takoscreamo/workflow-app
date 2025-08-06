<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_can_upload_pdf_file()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'original_name',
                    'file_path',
                    'file_size',
                    'mime_type',
                    'created_at'
                ]);

        $this->assertDatabaseHas('files', [
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf'
        ]);

        // レスポンスからファイルパスを取得
        $filePath = $response->json('file_path');
        Storage::disk('public')->assertExists($filePath);
    }

    public function test_can_upload_large_pdf_file()
    {
        $file = UploadedFile::fake()->create('large_document.pdf', 2048, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(201);

        // レスポンスからファイルパスを取得
        $filePath = $response->json('file_path');
        Storage::disk('public')->assertExists($filePath);
    }

    public function test_rejects_non_pdf_files()
    {
        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);

        Storage::disk('public')->assertMissing('uploads/' . $file->hashName());
    }

    public function test_rejects_files_without_extension()
    {
        $file = UploadedFile::fake()->create('document', 100, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);
    }

    public function test_rejects_files_with_invalid_extension()
    {
        $file = UploadedFile::fake()->create('document.pdf.txt', 100, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);
    }

    public function test_rejects_files_exceeding_size_limit()
    {
        $file = UploadedFile::fake()->create('large_document.pdf', 10240 * 1024, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);
    }

    public function test_rejects_request_without_file()
    {
        $response = $this->postJson('/api/files/upload', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);
    }

    public function test_rejects_multiple_files()
    {
        $file1 = UploadedFile::fake()->create('document1.pdf', 100, 'application/pdf');
        $file2 = UploadedFile::fake()->create('document2.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => [$file1, $file2]
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);
    }

    public function test_handles_malicious_filename()
    {
        $file = UploadedFile::fake()->create('../../../malicious.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(201);

        // ファイル名が安全に処理されていることを確認
        $this->assertDatabaseHas('files', [
            'original_name' => 'malicious.pdf'
        ]);
    }

    public function test_uploads_file_with_japanese_filename()
    {
        $file = UploadedFile::fake()->create('日本語ファイル.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('files', [
            'original_name' => '日本語ファイル.pdf'
        ]);
    }

    public function test_uploads_file_with_spaces_in_filename()
    {
        $file = UploadedFile::fake()->create('my document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('files', [
            'original_name' => 'my document.pdf'
        ]);
    }
}
