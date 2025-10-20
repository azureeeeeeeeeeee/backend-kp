<?php

use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PPDBSettingController; 
use App\Http\Controllers\TeacherController;
use App\Models\Admission;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/forgot-password', [AuthController::class, 'changePasswords']);
});

Route::group(['prefix'=> 'news'], function () {
    Route::post('', [NewsController::class, 'create'])->middleware('auth:sanctum');
    Route::put('/{id}', [NewsController::class,'update'])->middleware('auth:sanctum');
    Route::delete('/{id}', [NewsController::class, 'destroy'])->middleware('auth:sanctum');
    Route::get('/{id}', [NewsController::class, 'show']);
    Route::get('', [NewsController::class, 'index']);
});

Route::group(['prefix' => 'gallery'], function () {
    Route::post('', [GalleryController::class, 'addActivity'])->middleware('auth:sanctum');
    Route::put('/{id}', [GalleryController::class, 'editActivity'])->middleware('auth:sanctum');
    Route::delete('/{id}', [GalleryController::class, 'deleteActivity'])->middleware('auth:sanctum');

    Route::get('/{id}', [GalleryController::class, 'getSingleActivity']);
    Route::get('', [GalleryController::class, 'getAllActivity']);

    Route::post('/{id}/media', [GalleryController::class, 'addImage'])->middleware('auth:sanctum');
    Route::delete('/{id}/media', [GalleryController::class, 'deleteImage'])->middleware('auth:sanctum');
});

Route::group(['prefix' => 'facilities'], function () {
    Route::get('', [FacilityController::class, 'index']);
    Route::get('/{id}', [FacilityController::class, 'show']);
    Route::post('', [FacilityController::class, 'store'])->middleware('auth:sanctum');
    Route::post('/{id}', [FacilityController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/{id}', [FacilityController::class, 'destroy'])->middleware('auth:sanctum');
});

Route::group(['prefix' => 'admission'], function () {
    // ✨ semua orang bisa daftar & cek status tanpa login
    Route::post('', [AdmissionController::class, 'store']);
    Route::post('/{code}/check', [AdmissionController::class, 'checkAdmissionStatus']);

    // ✨ hanya admin (pakai sanctum) yang bisa kelola data
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('', [AdmissionController::class, 'index']);
        Route::get('/{id}', [AdmissionController::class, 'show']);
        Route::put('/{id}', [AdmissionController::class, 'update']);
        Route::delete('/{id}', [AdmissionController::class, 'destroy']);
        Route::get('/data/filter', [AdmissionController::class, 'filter']);
    });
});

Route::group(['prefix' => 'teachers'], function () {
    Route::get('', [TeacherController::class, 'index']);
    Route::get('/{id}', [TeacherController::class, 'show']);
    Route::post('', [TeacherController::class, 'store'])->middleware('auth:sanctum');
    Route::put('/{id}', [TeacherController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/{id}', [TeacherController::class, 'destroy'])->middleware('auth:sanctum');
});

// Routes PPDB Settings
Route::get('/ppdb-setting', [PPDBSettingController::class, 'show']);
Route::post('/ppdb-setting', [PPDBSettingController::class, 'update'])->middleware('auth:sanctum');