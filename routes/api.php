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
    Route::get('color', [ProjectController::class, 'indexColor']);
});

Route::group(['prefix' => 'private', 'middleware' => ['auth:sanctum']], function () {
    Route::post('new-color', [ProjectController::class, 'addColor']);
    Route::post('new-project', [ProjectController::class, 'addProject']);
    Route::post('edit-project/{id}', [ProjectController::class, 'updateProject']);
    Route::delete('del-project/{id}', [ProjectController::class, 'deleteProject']);
    Route::post('new-tech', [ProjectController::class, 'addTech']);
    Route::post('edit-tech/{id}', [ProjectController::class, 'updateTechs']);
    Route::delete('del-tech/{id}', [ProjectController::class, 'deleteTechs']);
    Route::get('user', [ProjectController::class, 'getUser']);
    Route::get('user/{id}', [ProjectController::class, 'getUserById']);
    Route::get('pp/{filename}', [ProjectController::class, 'getPP']);
});
