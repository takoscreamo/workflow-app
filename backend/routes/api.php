<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\FileController;
use Illuminate\Http\JsonResponse;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ワークフロー関連のAPIルート
Route::prefix('workflows')->group(function () {
    Route::get('/', [WorkflowController::class, 'index']);
    Route::post('/', [WorkflowController::class, 'store']);
    Route::get('/{id}', [WorkflowController::class, 'show']);
    Route::put('/{id}', [WorkflowController::class, 'update']);
    Route::delete('/{id}', [WorkflowController::class, 'destroy']);
    Route::post('/{id}/nodes', [WorkflowController::class, 'addNode']);
    Route::delete('/{workflowId}/nodes/{nodeId}', [WorkflowController::class, 'deleteNode']);
    Route::post('/{id}/run', [WorkflowController::class, 'runWorkflow']);
    Route::get('/execution/{sessionId}', [WorkflowController::class, 'getExecutionStatus']);
});

// ファイルアップロード関連のAPIルート
Route::prefix('files')->group(function () {
    Route::post('/upload', [FileController::class, 'upload']);
});

// ヘルスチェック用のAPIルート
Route::get('/health', function (): JsonResponse {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

// Queue monitoring routes
Route::prefix('queue')->group(function () {
    Route::get('/status', [App\Http\Controllers\QueueController::class, 'status']);
    Route::post('/restart', [App\Http\Controllers\QueueController::class, 'restart']);
    Route::post('/clear-failed', [App\Http\Controllers\QueueController::class, 'clearFailed']);
});
