<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegistryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Dryad Registry API Routes
|--------------------------------------------------------------------------
*/

// Registry API endpoints
Route::prefix('registry')->group(function () {
    // Publish a new package version
    Route::post('/publish', [RegistryController::class, 'publish']);
    
    // List all packages
    Route::get('/packages', [RegistryController::class, 'listPackages']);
    
    // Get package information
    Route::get('/packages/{packageName}', [RegistryController::class, 'getPackage']);
    
    // Download specific package version
    Route::get('/packages/{packageName}/{version}', [RegistryController::class, 'downloadPackage']);
});