<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\UserPlantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::post('/register', function (Request $request) {

//     $token = $request->user()->createToken($request->token_name);

 

//     return ['token' => $token->plainTextToken];

// });

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/plant', [PlantController::class, 'index']);  
Route::post('/plant', [PlantController::class, 'store']);  
Route::get('/plant/{name}', [PlantController::class, 'show']);  
Route::delete('/plant/{id}', [PlantController::class, 'destroy']);  

Route::post('/user/plant', [UserPlantController::class, 'store'])->middleware('auth:sanctum');  
Route::get('/user/plants', [UserPlantController::class, 'index'])->middleware('auth:sanctum');  
Route::delete('/user/plant/{id}', [UserPlantController::class, 'destroy'])->middleware('auth:sanctum');  

