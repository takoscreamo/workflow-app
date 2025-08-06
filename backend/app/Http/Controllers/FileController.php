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
            'file' => 'required|file|mimes:pdf|max:10240', // 最大10MB
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();

        // ファイルを保存
        $path = $file->storeAs('uploads', $filename, 'public');

        return response()->json([
            'message' => 'ファイルがアップロードされました',
            'filename' => $filename,
            'path' => $path,
            'size' => $file->getSize(),
        ], 201);
    }
}
