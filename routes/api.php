<?php

use App\Http\Controllers\Api\AuthenticateController;
use App\Http\Controllers\ChatGroupController;
use App\Http\Controllers\ChatMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/login', [AuthenticateController::class, 'login']);
Route::post('chat_group', [ChatGroupController::class, 'store'])->name('chat_group.store')->middleware('auth:sanctum');
Route::post('chat_group/{group_id}/message', [ChatMessageController::class, 'store'])->name('chat_group.message.store');