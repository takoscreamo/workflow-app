<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * PDFファイルをアップロード
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240', // 最大10MB、PDFファイルのみ
        ]);

                $file = $request->file('file');

        // ファイル名の拡張子をチェック
        $extension = $file->getClientOriginalExtension();
        if ($extension !== 'pdf') {
            return response()->json([
                'message' => 'PDFファイルのみアップロード可能です',
                'errors' => [
                    'file' => ['PDFファイルのみアップロード可能です']
                ]
            ], 422);
        }
        $filename = time() . '_' . $file->getClientOriginalName();

        // ファイルを保存
        $path = $file->storeAs('uploads', $filename, 'public');

        // データベースにファイル情報を保存
        $fileModel = \App\Infrastructure\Models\FileModel::create([
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return response()->json([
            'id' => $fileModel->id,
            'original_name' => $fileModel->original_name,
            'file_path' => $fileModel->file_path,
            'file_size' => $fileModel->file_size,
            'mime_type' => $fileModel->mime_type,
            'created_at' => $fileModel->created_at
        ], 201);
    }
}
