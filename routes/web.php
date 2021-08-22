<?php

use App\Http\Controllers\ManagerBoardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionFormController;
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

Route::prefix('questions')->group(function () {
    Route::prefix('answer')->group(function () {
        Route::get('/{id}', [QuestionFormController::class, 'answerShow'])->name('question.answer.show');
        Route::post('/{id}', [QuestionFormController::class, 'answerEnd'])->name('question.answer.end');
    });

    Route::prefix('ask')->group(function () {
        Route::get('/', [QuestionFormController::class, 'askShow'])->name('question.ask.show');
        Route::post('/', [QuestionFormController::class, 'askEnd'])->name('question.ask.end');
    });
});

Route::get('/dakboard/{id?}', [ManagerBoardController::class, 'show']);
Route::group(['prefix' => 'board'], function () {
    Route::group(['prefix' => 'microsoft'], function () {
        //https://portal.cpvs.moscow/board/microsoft/signin
        Route::get('/signin', [ManagerBoardController::class, 'microsoftSignIn']);
        Route::get('/signout/{id}', [ManagerBoardController::class, 'microsoftSignOut']);
        Route::get('callback', [ManagerBoardController::class, 'microsoftCallback']);
    });
});

Route::get('/{any}', [ApplicationController::class, 'index'])->where('any', '.*');
