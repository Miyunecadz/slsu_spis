<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConcernController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\ScholarController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\SMSBlastController;
use App\Models\AcademicYear;
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
        
        Route::post('/changePasswordAdmin', 'changePasswordAdmin')->middleware('auth');
        Route::post('/changePasswordScholar', 'changePasswordScholar')->middleware('auth');
    });
});



Route::middleware('auth')->group(function(){
    Route::prefix('scholars')->controller(ScholarController::class)->group(function(){
        Route::get('/', 'index');
        Route::get('/recipient', 'recipient'); //added for event recipient list
        Route::get('/academic-year', 'getAcademicYears');
        Route::get('/generate-report', 'getReport');
        Route::get('/semesterScholar', 'getTotalScholarsSemester');
        Route::get('/{id_number}', 'show');
        Route::post('/qualify', 'qualifyScholar');
        Route::post('/', 'store');
        Route::put('/{id_number}', 'update');
        Route::delete('/{id_number}', 'destroy');
    });

    Route::prefix('events')->controller(EventController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });

    Route::prefix('scholarship')->controller(ScholarshipController::class)->group(function() {
        Route::get('/', 'index');
        Route::get('/counts', 'scholarCounts');
        Route::get('/totalScholars', 'getTotalScholars');
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
    });

    Route::prefix('documents')->controller(DocumentController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/search', 'search'); //added search
        Route::get('/check/{id}', 'checkScholarQualification');
        Route::get('/checklist/{id_number}', 'getCheckList');
        Route::put('/{document_history}', 'update'); //added update
        Route::post('/', 'upload');
        Route::delete('/', 'delete');
    });

    Route::prefix('concern')->controller(ConcernController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/search', 'search');
        Route::get('/scholar/{scholar_id}', 'scholarConcern');
        Route::post('/', 'store');
        Route::post('/reply/{concern_id}', 'storeReply');
        Route::delete('/', 'destroy');
    });

    Route::prefix('requirements')->controller(RequirementController::class)->group(function() {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });

    Route::prefix('admin')->controller(AdminController::class)->group(function () {
        Route::put('/', 'update');
    });

    Route::prefix('academic')->controller(AcademicYearController::class)->group(function() {
        Route::get('/', 'index');
        Route::get('/active-year', 'show');
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
    });

    Route::get('/download', [DocumentController:: class, 'download']); //added download
    Route::post('/sms', SMSBlastController::class);
});


