<?php

use App\Http\Livewire\ChatRoom;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // CHAT ROUTE
});

Route::middleware('auth')->group(function () {
    
    Route::get('/chat/fetch/{user}', [ChatController::class, 'fetch']);
    Route::post('/chat/send', [ChatController::class, 'send']);
    Route::get('/chat/{user}', ChatRoom::class)->name('chat');
    Route::get('/chat/check-latest', function () {
        return \App\Models\Message::where('to_id', auth()->id())
        ->latest()
        ->first();
    });

});


require __DIR__.'/auth.php';
