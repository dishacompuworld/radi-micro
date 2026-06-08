<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ServerStats;
use App\Http\Controllers\MicrotikController;
use App\Http\Controllers\PPPoEController;
use App\Http\Controllers\FetchapiController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\FetchSNMP;
use App\Http\Controllers\WaController;
use App\Http\Controllers\Auth\PermissionController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\AlertMessageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PrtgApiController;


Route::get('/', function () {
    return view('auth.login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {


Route::get('dashboard',[DashboardController::class,'index'])->name('dashboard');
    Route::get('',[DashboardController::class,'Index']);

    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/logout', [ProfileController::class, 'logout'])->name('logout');

    // Route::resource('users', UserController::class);

    Route::resource('users',UserController::class);

    // Route::get('profile',[UserController::class,'profile'])->name('profile');
    Route::get('profileedit',[UserController::class,'edit'])->name('profile.edit');
    Route::post('profile/{user}',[UserController::class,'updateProfile'])->name('profile.update');
    Route::put('profile/update-password/{user}',[UserController::class,'updatePassword'])->name('update-password');


    Route::resource('server', ServerController::class);
    Route::resource('permissions',PermissionController::class)->only(['index','store','update','destroy']);
    Route::resource('roles',RoleController::class);

    Route::get('stats', [ServerStats::class, 'index'])->name('stats.index');
    Route::get('stats/{id}', [ServerController::class, 'show'])->name('stats.show');

    Route::get('system-health', [MicrotikController::class, 'getSystemHealth'])->name('microtik.system.health');
    Route::get('ip-neighbors', [MicrotikController::class, 'getIpNeighbors'])->name('microtik.ip.neighbors');
    Route::get('shedule', [MicrotikController::class, 'shedule'])->name('shedule.show');
    Route::get('scripts', [MicrotikController::class, 'readScripts'])->name('microtik.scripts');
    Route::get('showservices', [MicrotikController::class, 'showServices'])->name('show.services');
    Route::get('logs', [MicrotikController::class, 'viewLogs'])->name('microtik.logs');
    Route::get('log', [MicrotikController::class, 'viewLog'])->name('microtik.log');
    Route::get('systemhistory', [MicrotikController::class, 'getSystemHistory'])->name('system.history');
    Route::get('viewCommand', [MicrotikController::class, 'viewCommand'])->name('view.command');

    Route::get('pppoe/allactivenew', [PPPoEController::class, 'allactivenew'])->name('pppoe.allactivenew');

    Route::get('locationdetails',[FetchapiController::class, 'locationdetails'])->name('locationdetails.show');
    Route::get('showsubscriber',[FetchapiController::class, 'subscriberDetails'])->name('subscriber.show');
    Route::get('subscribermicrotik',[FetchapiController::class, 'subscriberDtlFromMicrotik'])->name('subscriber.microtik');
    Route::get('testapi', [FetchapiController::class, 'testapi'])->name('testapi.show');
    Route::get('accessrequest', [FetchapiController::class, 'accessrequest'])->name('show.accesslogs');
    Route::get('adminaccessrequest', [FetchapiController::class, 'adminaccesslog'])->name('admin.accesslogs');
    Route::get('subscriberaccessrequest', [FetchapiController::class, 'subscriberaccessrequest'])->name('subscriber.accessrequest');
    Route::get('subscriberaccessrequestdelete', [FetchapiController::class, 'subscriberaccessrequestdelete'])->name('subscriber.accessrequest.delete');
    Route::get('onlineusers', [FetchapiController::class, 'onlineradius'])->name('show.onlineusers');
    Route::get('resetmac', [FetchapiController::class, 'resetmac'])->name('reset.mac');
    Route::get('macreset', [FetchapiController::class, 'macreset'])->name('mac.reset');
    Route::get('enablesubscriber', [FetchapiController::class, 'enablesubscriber'])->name('enable.subscriber');
    Route::get('disablesubscriber', [FetchapiController::class, 'disablesubscriber'])->name('disable.subscriber');
    Route::get('speedchange', [FetchapiController::class, 'overrightspeed'])->name('speed.change');
    Route::get('searchsubscriber', [FetchapiController::class, 'searchsubscriber'])->name('search.subscriber');
    Route::get('searchsubscriberall', [FetchapiController::class, 'searchsubscriberall'])->name('search.subscriberall');
    // Route::get('allsubcount', [FetchapiController::class, 'allactiveusers'])->name('all.activeuser');
    
    //findmacvendor
    Route::get('findmacvendor', [FetchapiController::class, 'findmacvendor'])->name('find.mac.vendor');
    Route::get('findmac', [FetchapiController::class, 'findmac'])->name('find.mac');


    Route::get('location',[LocationController::class, 'show'])->name('location.show');
    Route::get('insertlocation/{name}/{url}',[LocationController::class, 'insert'])->name('location.insert');
    Route::get('deletelocation/{name}',[LocationController::class, 'deleted'])->name('location.delete');


    Route::get('packages', [PackageController::class, 'show'])->name('packages.show');
    Route::post('insertdeletepackage',[PackageController::class, 'insertdelete'])->name('package.insertdelete');

    
    Route::get('fetchontnames', [FetchSNMP::class, 'getontnames'])->name('get.ont.names');
    Route::get('fetchontpowers', [FetchSNMP::class, 'getontpowers'])->name('get.ont.powers');
    Route::get('insertopintodb', [FetchSNMP::class, 'insertintodb'])->name('insertop.into.db');
    Route::get('showopticalpowers', [FetchSNMP::class, 'showop'])->name('show.opticalpowers');
    Route::get('assignont', [FetchSNMP::class, 'assignpower'])->name('assign.ont');
    Route::get('getontpower', [FetchSNMP::class, 'getontpower'])->name('refresh.ont.power');
    Route::get('updateop', [FetchSNMP::class, 'updateop'])->name('update.ont.power');
    Route::get('deleteont', [FetchSNMP::class, 'deleteont'])->name('delete.ont');
    Route::get('editont', [FetchSNMP::class, 'renameont'])->name('edit.ont');
    Route::get('addont', [FetchSNMP::class, 'addont'])->name('add.ont');
    Route::get('deregister', [FetchSNMP::class, 'deregistont'])->name('de.register');
    Route::get('register', [FetchSNMP::class, 'registont'])->name('ont.register');
    Route::get('rebootont', [FetchSNMP::class, 'rebootont'])->name('reboot.ont');

    Route::get('whatsappmsg', [WaController::class, 'sendtext'])->name('whatsapp.msg');
    Route::post('send-image', [WaController::class, 'sendImage'])->name('send.image');

    Route::get('alllogs', [LogController::class, 'show'])->name('show.alllogs');
    Route::get('delete', [LogController::class, 'delete'])->name('delete.log');
    Route::get('showlogs', [LogController::class, 'showlogs'])->name('show.log');

    Route::post('alert-messages', [AlertMessageController::class, 'store'])->name('alert.store');
    Route::get('alert-messages', [AlertMessageController::class, 'index'])->name('alert.index');
    Route::get('alert-messages/create', [AlertMessageController::class, 'create'])->name('alert.create');
    Route::get('alert-messages/{id}', [AlertMessageController::class, 'show'])->name('alert.show');
    Route::get('alert-messages/{id}/edit', [AlertMessageController::class, 'edit'])->name('alert.edit');
    Route::put('alert-messages/{id}', [AlertMessageController::class, 'update'])->name('alert.update');
    Route::delete('alert-messages/{id}', [AlertMessageController::class, 'destroy'])->name('alert.destroy');

    Route::get('prtgapitest', [PrtgApiController::class, 'getMsebStatusData'])->name('prtg.api');
    Route::get('historygraph', [PrtgApiController::class, 'historygraph'])->name('history.graph');
    Route::get('allsensors', [PrtgApiController::class, 'allsensors'])->name('all.sensors');
    Route::get('/svg1', [PrtgApiController::class, 'generateSVG1']); 
    Route::get('/svg2', [PrtgApiController::class, 'generateSVG2']);
    Route::get('livegraph', [PrtgApiController::class, 'liveGraph'])->name('live.graph');
    Route::get('messsages', [PrtgApiController::class, 'getMessages'])->name('ptrg.messsages');
    // Route::get('live-graph', [PrtgApiController::class, 'liveGraph'])->name('live.graph');
    Route::get('live-graph', [PrtgApiController::class, 'liveGraph'])->name('live.graph');
    Route::get('checkmseb',[PrtgApiController::class, 'getMsebStatusData'])->name('check.mseb');
    Route::get('mseb-info', [PrtgApiController::class, 'getMsebInfoAjax'])->name('admin.dashboard.mseb');

    Route::get('settings',[SettingController::class,'index'])->name('settings');

});

require __DIR__.'/auth.php';
