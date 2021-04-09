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

Route::middleware('auth:sanctum')->group(function(){
    Route::resource('/news', NewsController::class)->except('index');
    Route::resource('/events', EventController::class);

    Route::resource('news.comments', NewsCommentController::class);
    Route::resource('events.comments', EventCommentController::class);
});
