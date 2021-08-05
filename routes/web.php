<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/dakboard/{id?}', [ManagerBoardController::class, 'show']);
//https://portal.cpvs.moscow/board/microsoft/signin

Route::group(['prefix' => 'board'],function () {
    Route::group(['prefix' => 'microsoft'], function () {
        Route::get('/signin', [ManagerBoardController::class, 'microsoftSignIn']);
        Route::get('/signout/{id}', [ManagerBoardController::class, 'microsoftSignOut']);
        Route::get('callback', [ManagerBoardController::class, 'microsoftCallback']);
    });
});

Route::get('/{any}', [ApplicationController::class, 'index'])->where('any', '.*');
