<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('conversation', ConversationController::class);
    Route::post('/conversation/{conversation}/send', [ConversationController::class, 'sendMessage'])->name('conversation.send');
    Route::post('/conversation/{conversation}/send-file', [ConversationController::class, 'sendMessageWithFiles'])->name('conversation.send-file');

    Route::resource('knowledge', KnowledgeController::class);
    Route::get('/knowledge/{knowledge}/download', [KnowledgeController::class, 'download'])->name('knowledge.download');
    Route::patch('/knowledge/{knowledge}/toggle', [KnowledgeController::class, 'toggle'])->name('knowledge.toggle');
});

require __DIR__.'/auth.php';
