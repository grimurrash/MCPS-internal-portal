<?php

use App\Http\Controllers\AccountSettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Documents\DocumentController;
use App\Http\Controllers\Documents\DocumentTemplateController;
use App\Http\Controllers\Documents\DocumentTypeController;
use App\Http\Controllers\Events\WordCloudController;
use App\Http\Controllers\FileStorageController;
use App\Http\Controllers\Management\DepartmentController;
use App\Http\Controllers\Management\EmployeeController;
use App\Http\Controllers\Management\HelpDeskController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Management\VisitEventController;
use App\Http\Controllers\ManagerBoardController;
use App\Http\Controllers\MCPSEventsController;
use App\Http\Controllers\Organizers\OrganizerController;
use App\Http\Controllers\QuestionFormController;
use App\Http\Controllers\ScriptController;
use Illuminate\Support\Facades\Route;

Route::get('question/answer/{id}/text', [QuestionFormController::class, 'answerText']);

Route::prefix('word-cloud')->group(function () {
    Route::get('/{id}/settings', [WordCloudController::class, 'getWordCloudSetting']);
    Route::get('/{id}/answer', [WordCloudController::class, 'getWordCloudAnswer']);
});

Route::post('events/admin/participant/{id}/sendQrCodes', [MCPSEventsController::class, 'sendQrCode']);

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

Route::group(['prefix' => 'helpdesk'], function () {
    Route::get('options', [HelpDeskController::class, 'createOptionsList']);
    Route::post('create', [HelpDeskController::class, 'createHelpDeskRequest']);
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

    Route::group(['prefix' => 'helpdesk'], function () {
        Route::get('tableOptions', [HelpDeskController::class, 'tableOptionsList']);
        Route::get('/', [HelpDeskController::class, 'index']);
        Route::get('/{id}', [HelpDeskController::class, 'show']);
        Route::post('/{id}', [HelpDeskController::class, 'update']);
        Route::post('/{id}/status', [HelpDeskController::class, 'changeHelpDeskRequestStatus']);
        Route::post('/{id}/executor', [HelpDeskController::class, 'acceptHelpDeskRequest']);
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

    Route::prefix('organizer')->group(function () {
        Route::get('/{id}',[OrganizerController::class, 'show']);
        Route::group(['middleware' => ['logger:organizer']], function () {
            Route::post('/menu', [OrganizerController::class, 'storeOrganizerMenu']);
            Route::post('/menu/{id}', [OrganizerController::class, 'updateOrganizerMenu']);
            Route::delete('/menu/{id}', [OrganizerController::class, 'deleteOrganizerMenu']);
            Route::post('/item', [OrganizerController::class, 'storeOrganizerItem']);
            Route::post('/item/{id}', [OrganizerController::class, 'updateOrganizerItem']);
            Route::delete('/item/{id}', [OrganizerController::class, 'deleteOrganizerItem']);
        });
    });

    // MCPS Events
    Route::group(['prefix' => 'events'], function () {
        Route::group(['prefix' => 'wordcloud'], function () {
            Route::get('/', [WordCloudController::class, 'index']);
            Route::post('/', [WordCloudController::class, 'store']);
            Route::get('{id}',[WordCloudController::class, 'show']);
            Route::post('/{id}', [WordCloudController::class, 'update']);
            Route::delete('/{id}', [WordCloudController::class, 'delete']);
            Route::post('/{id}/clear',[WordCloudController::class, 'clear']);
            Route::post('/{id}/exportToExcel', [WordCloudController::class, 'exportWordCloudAnswerToExcel']);
            Route::post('/{id}/exportToGoogle', [WordCloudController::class, 'exportWordCloudAnswerToGoogleSheep']);
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
                Route::post('/importList', [UserController::class, 'listStore']);
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

Route::group(['prefix' => 'scripts'], function () {
    Route::get('importFounderRepresentative', [ScriptController::class, 'importFounderRepresentative']);
    Route::get('importDirectorGTOData', [ScriptController::class, 'importDirectorGTOData']);
});

Route::group(['prefix' => 'board'], function () {
    Route::get('yandexWeatherApi', [ManagerBoardController::class, 'yandexWeather']);
    Route::get('getNews', [ManagerBoardController::class, 'getNews']);
    Route::get('getAllNews', [ManagerBoardController::class, 'getAllNews']);
    Route::get('getCalendar', [ManagerBoardController::class, 'getCalendar']);
    Route::get('getDistance', [ManagerBoardController::class, 'getDistanceFromBoardBeforeOthersBuildings']);
});


