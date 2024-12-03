<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\RedirectToGame;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->to(route('dashboard'));
    }

    return redirect()->to(route('login'));
});

Route::redirect('/dashboard', '/games')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('games', [GameController::class, 'index'])->middleware(RedirectToGame::class);
    Route::resource('games', GameController::class)->only('store', 'show', 'update', 'destroy');

    Route::post('games/{game}/join', [GameController::class, 'join'])->name('games.join');
});

require __DIR__.'/auth.php';
