<?php

use App\Http\Controllers\AccountSettingController;
use App\Http\Controllers\Documents\DocumentController;
use App\Http\Controllers\Documents\DocumentTemplateController;
use App\Http\Controllers\Documents\DocumentTypeController;
use App\Http\Controllers\FileStorageController;
use App\Http\Controllers\Management\DepartmentController;
use App\Http\Controllers\Management\EmployeeController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Management\VisitEventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


//  Auth
Route::group(['prefix' => 'auth'], function () {

    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('user', [AuthController::class, 'user'])->name('userData');
    });

    //  Reset Password
    Route::group([
        'prefix' => 'password',
        'name' => 'password.',
    ], function () {
        Route::post('forgot', [AuthController::class, 'forgotPassword'])->name('forgot');
        Route::post('find/', [AuthController::class, 'findResetPassword'])->name('find');
        Route::post('reset', [AuthController::class, 'resetPassword'])->name('reset');
    });
});

Route::group(['middleware' => 'auth:api'], function () {

    Route::group(['prefix' => 'account-setting'], function () {
        Route::get('data', [AccountSettingController::class, 'data']);
        Route::group(['middleware' => 'logger:account-setting'], function () {
            Route::post('general/save-changes', [AccountSettingController::class, 'updateGeneral']);
            Route::post('password/save-changes', [AccountSettingController::class, 'updatePassword']);
            Route::post('info/save-changes', [AccountSettingController::class, 'updateInfo']);
        });
    });

    //  FileStorage
    Route::group(['prefix' => 'file-storage'], function () {
        Route::get('/', [FileStorageController::class, 'index']);
        Route::post('/', [FileStorageController::class, 'store']);
        Route::delete('/{id}', [FileStorageController::class, 'destroy']);
    });

    //  Document
    Route::group(['prefix' => 'documents'], function () {

        Route::post('/printExcelDataByExistentTemplate', [DocumentController::class, 'printExcelDataByExistentTemplate']);
        Route::post('/printExcelDataByTemplate', [DocumentController::class, 'printExcelDataByTemplate']);

        Route::get('/documentTypes', [DocumentTypeController::class, 'index']);

        Route::group(['prefix' => 'templates'], function () {
            Route::get('/', [DocumentTemplateController::class, 'index']);
            Route::get('/optionsList', [DocumentTemplateController::class, 'optionsList']);
            Route::group([
                'middleware' => ['permission:document_template', 'logger:document_templates']
            ], function () {
                Route::post('/', [DocumentTemplateController::class, 'store']);
                Route::post('/{id}', [DocumentTemplateController::class, 'update']);
                Route::delete('/{id}', [DocumentTemplateController::class, 'destroy']);
            });

        });
    });

    //  Management
    Route::group(['prefix' => 'management'], function () {
        Route::get('/rolesAndPermissions', [UserController::class, 'selectOptions']);

        //  User management
        Route::group([
            'prefix' => 'users',
            'middleware' => 'permission:all_manage'
        ], function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/options', [UserController::class, 'usersOptions']);
            Route::get('/{id}', [UserController::class, 'show']);

            Route::group(['middleware' => 'logger:management_users'], function () {
                Route::post('/', [UserController::class, 'store']);
                Route::post('/{id}', [UserController::class, 'update']);
                Route::delete('/{id}', [UserController::class, 'destroy']);
            });
        });

        //  Department
        Route::group(['prefix' => 'departments'], function () {
            Route::get('/options', [DepartmentController::class, 'optionsList']);
            Route::get('/', [DepartmentController::class, 'index']);
            Route::group(['middleware' => 'logger:departments'], function () {
                Route::post('/import', [DepartmentController::class, 'import']);
                Route::post('/{id}', [DepartmentController::class, 'update']);
            });
        });


        //  Employee
        Route::group(['prefix' => 'employees'], function () {
            Route::get('/options', [EmployeeController::class, 'optionsList']);
            Route::get('/optionsByUser/{userId}', [EmployeeController::class, 'optionsListByUser']);
            Route::get('/', [EmployeeController::class, 'index']);
            Route::get('/phoneBook', [EmployeeController::class, 'phoneBook']);
            Route::post('/phoneBook/import', [EmployeeController::class, 'importPhoneBook']);
            Route::group(['middleware' => 'logger:employees'], function () {
                Route::post('import', [EmployeeController::class, 'import']);
                Route::post('/{id}', [EmployeeController::class, 'update']);
            });
        });

        //  VisitEvent
        Route::group(['prefix' => 'visit-events'], function () {
            Route::get('/', [VisitEventController::class, 'index']);
            Route::get('/break', [VisitEventController::class, 'breakEvent']);
            Route::post('/exportAttendance', [VisitEventController::class, 'exportAttendance']);
            Route::post('/exportBreak', [VisitEventController::class, 'exportBreak']);
            Route::group(['middleware' => 'logger:visit_events'], function () {
                Route::post('import', [VisitEventController::class, 'import']);
                Route::post('/{id}', [VisitEventController::class, 'update']);
            });
        });
    });
});



