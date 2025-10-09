<?php

use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\NewsController;
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
    Route::post('/{id}', [NewsController::class,'update'])->middleware('auth:sanctum');
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

Route::group(['prefix' => 'facility'], function () {
    Route::get('', [FacilityController::class, 'index']);
    Route::get('/{id}', [FacilityController::class, 'show']);

    Route::post('', [FacilityController::class, 'store'])->middleware('auth:sanctum');
    Route::post('/{id}', [FacilityController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/{id}', [FacilityController::class, 'destroy'])->middleware('auth:sanctum');
});

Route::group(['prefix' => 'admission'], function () {
    Route::get('', [AdmissionController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/{id}', [AdmissionController::class, 'show'])->middleware('auth:sanctum');

    Route::post('', [AdmissionController::class, 'store'])->middleware('auth:sanctum');
    Route::put('/{id}', [AdmissionController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/{id}', [AdmissionController::class, 'destroy'])->middleware('auth:sanctum');

    Route::post('/{code}', [AdmissionController::class, 'checkAdmissionStatus']);
    Route::post('/filter', [AdmissionController::class, 'filter'])->middleware('auth:sanctum');
});