<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// use App\Http\Controllers\ServerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\FetchapiController;
use App\Http\Controllers\FetchSNMP;
use App\Http\Controllers\PPPoEController;
use App\Http\Controllers\PrtgApiController;
use App\Http\Controllers\ServerStats;
use App\Http\Controllers\MicrotikController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrtgWebhookController;


    // Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Route::get('servers', [AuthController::class, 'servers']);

    Route::group(['middleware' => ['auth:sanctum']], function () {
    
    //logout
    Route::post('logout', [AuthController::class, 'logout']);
    
    //users
    Route::get('user', [AuthController::class, 'getProfileapi']);
    
    //server
    Route::get('servers', [AuthController::class, 'servers']);
    Route::post('server/{id}', [AuthController::class, 'server']);

    //location
    Route::get('locations', [LocationController::class, 'showapi']);
    Route::post('location/{id}', [AuthController::class, 'location']);



    //Micotik Stats
    Route::get('serverstats/{id}', [ServerStats::class, 'api']);
    Route::get('activeusers', [PPPoEController::class, 'allactivenewapi']);
    Route::get('ping', [PPPoEController::class, 'realTimePingApi']);

    //radius
    Route::get('alllocationusers', [FetchapiController::class, 'searchsubscriberallapi']);
    Route::get('adminaccessrequest',[FetchapiController::class, 'adminaccesslogapi']);
    Route::post('macreset', [FetchapiController::class, 'macresetapi']);
    Route::post('disableuser', [FetchapiController::class, 'disablesubscriberapi']);
    Route::post('enableuser', [FetchapiController::class, 'enablesubscriberapi']);

    //prtg
    Route::get('prtgmessages', [PrtgApiController::class, 'getMessagesapi']);
    Route::get('msebstatus', [PrtgApiController::class, 'getMsebStatusDataapi']);

    //Optical Power Table
    Route::get('showopapi', [FetchSNMP::class, 'showopapi']);
    Route::get('rebootont',[FetchSNMP::class, 'rebootontapi']);
    Route::get('updateopont',[FetchSNMP::class, 'updateopapi']);
    Route::delete('deleteont',[FetchSNMP::class, 'deleteontapi']);
    Route::get('getontdetails', [FetchSNMP::class, 'getontdetailsapi']);
    Route::put('addont', [FetchSNMP::class, 'addontapi']);

    //userdata
    Route::get('userdata', [FetchapiController::class, 'subscriberDtlFromMicrotikapi']);

    //dashboard
    Route::get('dashboard', [DashboardController::class, 'dashboardapi']);

    //deleteactiveuser
    Route::delete('deleteactiveuser', [PPPoEController::class, 'deletapi']);

    //servermessages
    Route::get('servermessages', [MicrotikController::class, 'getSystemHistoryApi']);

    //logs
    Route::get('logs', [LogController::class, 'showlogsapi']);
    Route::get('alllogs', [LogController::class, 'alllogsapi']);

});

// PRTG authenticates with the webhook token, not Sanctum.
Route::post('prtg-webhook', [PrtgWebhookController::class, 'handle']);

// Route::get('userdetails', [FetchapiController::class, 'subscriberDtlFromMicrotikapi']);
