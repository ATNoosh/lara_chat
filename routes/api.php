<?php

use App\Http\Controllers\Api\AuthenticateController;
use App\Http\Controllers\ChatGroupController;
use App\Http\Controllers\ChatMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication routes
Route::post('/auth/login', [AuthenticateController::class, 'login']);
Route::post('/auth/register', [AuthenticateController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Users routes
    Route::get('users', function () {
        $users = \App\Models\User::where('id', '!=', auth()->id())->select('id', 'name', 'email')->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    });

    // Chat Group routes
    Route::get('chat_groups', [ChatGroupController::class, 'index'])->name('chat_groups.index');
    Route::post('chat_groups', [ChatGroupController::class, 'store'])->name('chat_groups.store');
    Route::get('chat_groups/{chatGroup:uuid}', [ChatGroupController::class, 'show'])->name('chat_groups.show');
    Route::put('chat_groups/{chatGroup:uuid}', [ChatGroupController::class, 'update'])->name('chat_groups.update');
    Route::delete('chat_groups/{chatGroup:uuid}', [ChatGroupController::class, 'destroy'])->name('chat_groups.destroy');

    // Chat Message routes
    Route::get('chat_groups/{chatGroup:uuid}/messages', [ChatMessageController::class, 'index'])->name('chat_messages.index');
    Route::post('chat_groups/{chatGroup:uuid}/messages', [ChatMessageController::class, 'store'])->name('chat_messages.store');
    Route::post('chat_groups/{chatGroup:uuid}/messages/read', [ChatMessageController::class, 'markAsRead'])->name('chat_messages.read');
    Route::post('chat_groups/{chatGroup:uuid}/messages/{chatMessage}/read', [ChatMessageController::class, 'markMessageAsRead'])->name('chat_messages.single_read');
    Route::post('chat_groups/{chatGroup:uuid}/typing', [ChatMessageController::class, 'updateTypingStatus'])->name('chat_messages.typing');
    Route::get('chat_messages/{chatMessage}', [ChatMessageController::class, 'show'])->name('chat_messages.show');
    Route::put('chat_messages/{chatMessage}', [ChatMessageController::class, 'update'])->name('chat_messages.update');
    Route::delete('chat_messages/{chatMessage}', [ChatMessageController::class, 'destroy'])->name('chat_messages.destroy');
});
