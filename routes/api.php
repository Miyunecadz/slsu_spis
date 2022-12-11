<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ScholarController;
use App\Http\Controllers\SMSBlastController;
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

Route::prefix('auth')->group(function(){

    Route::controller(AuthController::class)->group(function(){
        Route::post('/login', 'login');
        Route::middleware('auth')->group(function(){
            Route::post('/logout', 'logout');
        });
    });

    Route::controller(PasswordController::class)->group(function(){
        Route::post('/password-request', 'changePasswordRequest');
    });
});



Route::middleware('auth')->group(function(){
    Route::prefix('scholars')->controller(ScholarController::class)->group(function(){
        Route::get('/', 'index');
        Route::get('/{id_number}', 'show');
        Route::post('/', 'store');
        Route::put('/{id_number}', 'update');
        Route::delete('/{id_number}', 'destroy');
    });

    Route::prefix('events')->controller(EventController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
    });

    Route::post('/sms', SMSBlastController::class);
});


