<?php

use Illuminate\Http\Request;

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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::namespace('Admin')->prefix('admin/v1')->group(function () {
    Route::get('index/{id}', 'InfoController@show');
    Route::post('index', 'InfoController@index');
    Route::post('info', 'InfoController@store');
    Route::put('info/{id}', 'InfoController@edit');
    Route::post('apiGetUploadToken', 'InfoController@apiGetUploadToken');
    Route::post('infoEditNum', 'InfoController@infoEditNum');
    Route::delete('infoDelete/{id}', 'InfoController@infoDelete');


    Route::get('setting', 'InfoController@setting');
    Route::post('infoEditSetting', 'InfoController@infoEditSetting');
});

Route::namespace('Admin')->prefix('info/v1')->group(function () {
    Route::post('index', 'InfoController@infoIndex');
    Route::get('infoData', 'InfoController@infoData');
    Route::post('infoVisit', 'InfoController@infoVisit');
    Route::get('index/{id}', 'InfoController@show');
    Route::post('infoSubmit', 'InfoController@infoSubmit');
});
