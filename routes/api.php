<?php

use App\Http\Controllers\EventCommentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NewsCommentController;
use App\Http\Controllers\NewsController;
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

Route::post('/news/{news}/comments', [NewsCommentController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/news/{news}/comments/{comment}', [NewsCommentController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/events', [EventController::class, 'index'])->middleware('auth:sanctum');
Route::post('/events', [EventController::class, 'store'])->middleware('auth:sanctum');
Route::put('/events/{event}', [EventController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/events/{event}', [EventController::class, 'destroy'])->middleware('auth:sanctum');

Route::post('/events/{event}/comments', [EventCommentController::class, 'store'])
    ->middleware('auth:sanctum');
Route::delete('/events/{event}/comments/{comment}', [EventCommentController::class, 'destroy'])
    ->middleware('auth:sanctum');
