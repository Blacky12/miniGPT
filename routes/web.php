<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;
use App\Http\Controllers\AskController;
use App\Http\Controllers\ConversationController;
use App\Services\ChatService;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\UserInstructionController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('ask.index');
    }
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Page d'accueil après connexion
    Route::get('/ask', [ConversationController::class, 'index'])->name('ask.index');

    // Routes pour les conversations
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::delete('/conversations/{conversation}', [ConversationController::class, 'destroy'])->name('conversations.destroy');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'storeMessage'])->name('conversations.messages.store');
    Route::post('/conversations', [ConversationController::class, 'storeSimple'])->name('conversations.storeSimple');
    Route::post('/conversations/{conversation}/stream', [ConversationController::class, 'sendMessageStream'])->name('conversations.stream');

    // Routes pour les instructions utilisateur (API seulement)
    Route::get('/user-instructions-api/active', [UserInstructionController::class, 'getActive'])->name('user-instructions.active');
    Route::post('/user-instructions', [UserInstructionController::class, 'store'])->name('user-instructions.store');
    Route::put('/user-instructions/{userInstruction}', [UserInstructionController::class, 'update'])->name('user-instructions.update');

    // Route pour la mise à jour du modèle
    Route::post('/model', [ModelController::class, 'update'])->name('model.update');
});
