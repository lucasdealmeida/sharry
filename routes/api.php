<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\NewsController;
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

Route::post('/login', LoginController::class);

Route::get('/news', [NewsController::class, 'index']);

Route::post('/news', [NewsController::class, 'store'])->middleware('auth:sanctum');
Route::put('/news/{news}', [NewsController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/news/{news}', [NewsController::class, 'destroy'])->middleware('auth:sanctum');
