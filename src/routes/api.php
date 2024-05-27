<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
Route::get('/', function () {
    return view('welcome');
});


Route::prefix('Auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'getUserData']);
    Route::post('/register/verify-code', [RegisterController::class, 'checkVerifyCode']);
    
    //Logout route
    Route::delete('/logout/{token}', [LogoutController::class, 'logout']);
});