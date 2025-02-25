<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/items', [ItemController::class, 'index'])->name('items.index');
Route::post('/items', [ItemController::class, 'store'])->name('items.store');
Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');

Route::get('/api/category-stats', [ItemController::class, 'genderStats'])->name('items.genderStats');
Route::get('/api/date-stats', [ItemController::class, 'aggregateStats'])->name('items.aggregateStats');
Route::get('/api/genderaggreg-stats', [ItemController::class, 'genderAggregStats'])->name('items.genderAggregStats');

require __DIR__ . '/auth.php';
