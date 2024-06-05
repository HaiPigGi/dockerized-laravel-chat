<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Chatting\ChattingController;
Route::get('/', function () {
    return view('welcome');
});


Route::prefix('Auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'getUserData']);
    Route::post('/register/verify-code', [RegisterController::class, 'createUser']);

    //Login route
    Route::post('/login', [LoginController::class, 'getData']);
    Route::post('/login/verify-code', [LoginController::class, 'login']);
    
    //Logout route
    Route::delete('/logout/{token}', [LogoutController::class, 'logout']);
});

//make routes for chat
Route::prefix('chat')->group(function () {
    Route::post('/send-message', [ChattingController::class, 'sendMessage']);
    Route::get('/get-messages/{chatRoomId}', [ChattingController::class, 'getMessages']);
    Route::post('/create-room', [ChattingController::class, 'createRoom']);
});