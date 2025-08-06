<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\FileController;

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
    Route::post('/{id}/run', [WorkflowController::class, 'runWorkflow']);
});

// ファイルアップロード関連のAPIルート
Route::prefix('files')->group(function () {
    Route::post('/upload', [FileController::class, 'upload']);
});
