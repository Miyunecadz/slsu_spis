<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScholarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('auth')->controller(AuthController::class)->group(function(){
    Route::post('/login', 'login');

    Route::middleware('auth:sanctum')->group(function(){
        Route::post('/logout', 'logout');
    });
});

Route::middleware('auth:sanctum')->group(function(){
    Route::prefix('scholars')->controller(ScholarController::class)->group(function(){
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


