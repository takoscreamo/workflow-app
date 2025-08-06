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

    public function test_PDFファイルをアップロードできる()
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

    public function test_大きなPDFファイルをアップロードできる()
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

    public function test_PDF以外のファイルを拒否する()
    {
        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);

        Storage::disk('public')->assertMissing('uploads/' . $file->hashName());
    }

    public function test_拡張子がないファイルを拒否する()
    {
        $file = UploadedFile::fake()->create('document', 100, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);
    }

    public function test_無効な拡張子のファイルを拒否する()
    {
        $file = UploadedFile::fake()->create('document.pdf.txt', 100, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);
    }

    public function test_サイズ制限を超えるファイルを拒否する()
    {
        $file = UploadedFile::fake()->create('large_document.pdf', 10240 * 1024, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => $file
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);
    }

    public function test_ファイルなしのリクエストを拒否する()
    {
        $response = $this->postJson('/api/files/upload', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);
    }

    public function test_複数ファイルを拒否する()
    {
        $file1 = UploadedFile::fake()->create('document1.pdf', 100, 'application/pdf');
        $file2 = UploadedFile::fake()->create('document2.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/files/upload', [
            'file' => [$file1, $file2]
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['file']);
    }

    public function test_悪意のあるファイル名を処理する()
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

    public function test_日本語ファイル名でファイルをアップロードできる()
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

    public function test_スペースを含むファイル名でファイルをアップロードできる()
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
