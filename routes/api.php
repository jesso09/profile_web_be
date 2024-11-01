<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'public'], function () {
    Route::post('login', [ProjectController::class, 'login']);
    Route::get('index', [ProjectController::class, 'index']);
    Route::get('tech', [ProjectController::class, 'indexTech']);
});

Route::group(['prefix' => 'private', 'middleware' => ['auth:sanctum']], function () {
    Route::post('new-project', [ProjectController::class, 'addProject']);
    Route::post('new-tech', [ProjectController::class, 'addTech']);
    Route::get('user', [ProjectController::class, 'getUser']);
    Route::get('user/{id}', [ProjectController::class, 'getUserById']);
    Route::get('pp/{filename}', [ProjectController::class, 'getPP']);
});
