<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DeveloperController;

// Página inicial
Route::get('/', [HomeController::class, 'index'])->name('home');

// Autenticação
Auth::routes();

// Rota de home alternativa
Route::get('/home', [HomeController::class, 'index']);

// Pacotes
Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show');

// Busca
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Developer Dashboard (requer autenticação)
Route::middleware('auth')->group(function () {
    Route::get('/developer', [DeveloperController::class, 'dashboard'])->name('developer.dashboard');
    Route::get('/developer/publish', [DeveloperController::class, 'create'])->name('developer.create');
    Route::post('/developer/publish', [DeveloperController::class, 'store'])->name('developer.store');
    Route::get('/developer/packages/{package}', [DeveloperController::class, 'show'])->name('developer.show');
    Route::get('/developer/packages/{package}/edit', [DeveloperController::class, 'edit'])->name('developer.edit');
    Route::put('/developer/packages/{package}', [DeveloperController::class, 'update'])->name('developer.update');
    Route::delete('/developer/packages/{package}', [DeveloperController::class, 'destroy'])->name('developer.destroy');
});
