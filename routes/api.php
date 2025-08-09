<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;
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