<?php

use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\UploadFileController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// auth for all the next routes
Route::middleware('auth:sanctum')->group(function () {
    // projects routes
    Route::get('/data/{model}', [ResourceController::class, 'index']);
    Route::post('/data/{model}', [ResourceController::class, 'store']);
    Route::get('/data/{model}/{id}', [ResourceController::class, 'show']);
    Route::put('/data/{model}/{id}', [ResourceController::class, 'update']);
    Route::delete('/data/{model}/{id}', [ResourceController::class, 'destroy']);
    Route::get('/data/{model}/{id}/{children}', [ResourceController::class, 'indexChildren']);
    Route::post('/data/{model}/{id}/{children}', [ResourceController::class, 'storeChildren']);
    Route::delete('/data/{model}/{id}/{children}/{childId}', [ResourceController::class, 'destroyChildren']);

    Route::get('/start/{process}/{startEvent}', [WorkflowController::class, 'start']);
    Route::post('/start/{process}/{startEvent}', [WorkflowController::class, 'start']);
    Route::get('/call/{process}', [WorkflowController::class, 'callProcess']);
    Route::post('/call/{process}', [WorkflowController::class, 'callProcess']);
    Route::get('/open-start/{process}/{requestId}/{startEvent}', [WorkflowController::class, 'openStart']);
    Route::post('/open-start/{process}/{requestId}/{startEvent}', [WorkflowController::class, 'openStart']);
    Route::get('/open/{requestId}', [WorkflowController::class, 'open']);
    Route::get('/route/{requestId}', [WorkflowController::class, 'route']);
    Route::get('/message/{requestId}/{tokenId}/{ref}', [WorkflowController::class, 'message']);
    Route::get('/signal/{requestId}/{ref}', [WorkflowController::class, 'signal']);
    Route::post('/complete/{requestId}/{tokenId}', [WorkflowController::class, 'complete']);
    Route::post('/update/{requestId}/{tokenId}', [WorkflowController::class, 'update']);
    Route::get('/history/{requestId}', [WorkflowController::class, 'history']);
    Route::get('/open-record/{recordId}', [WorkflowController::class, 'openRecord']);
    Route::get('/report', [ReportController::class, 'report']);
    Route::get('/report/excel', [ReportController::class, 'exportExcel']);

    Route::post('upload_file', [UploadFileController::class, 'upload']);
});
