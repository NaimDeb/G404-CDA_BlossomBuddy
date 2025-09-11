<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::post('/register', function (Request $request) {

//     $token = $request->user()->createToken($request->token_name);

 

//     return ['token' => $token->plainTextToken];

// });

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


