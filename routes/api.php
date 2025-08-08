<?php

use App\Http\Controllers\API\ConversationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('conversations/history/{conversation}', [ConversationController::class, 'getHistory']);

Route::middleware('throttle:api')->group(function () {
    Route::get('/', function () {
        return [
            'success' => true,
            'version' => '1.0.0',
        ];
    });


    Route::apiResource('conversations', ConversationController::class);
});
