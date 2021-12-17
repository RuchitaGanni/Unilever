<?php

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

/*Route::get('/', function () {
    return view('welcome');
});
*/

View::composer('layouts.sideview', 'App\Http\Controllers\HeaderController');
Route::get('/','WelcomeController@index');
Route::any('userlocations','DashboardController@getUserLocations');
Route::any('userlocationssave/{options}','DashboardController@saveUserLocations');
/* checking for location match in app and  portal 
Route::any('checkingLocationOnLoop','WelcomeController@checkingLocationOnLoop');
*/
Route::any('sendmail', 'WelcomeController@sendmail');
Route::any('matMaster', 'WelcomeController@matMaster');

Route::get('/send-email','WelcomeController@sendEmail');
Route::any('excel_import','DemoController@excel_import');
Route::post('export_Data','DemoController@export_Data')->name('export_Data');
Route::any('reset','DemoController@reset')->name('reset');
Route::get('download/{type}','DemoController@download');

Route::get('about', function()
{
	return View::make('about');
});


//Consolidates all the routes from routes folder
foreach (glob(__DIR__ . '/web/*.php') as $route_file)
{
require $route_file;
}



