<?php

Route::get('login','AuthenticationController@index');
Route::put('login/checkAuth','AuthenticationController@checkAuth');
Route::get('logout','AuthenticationController@logout');
Route::post('forgot','AuthenticationController@forgot');
Route::get('password/reset/{token}','AuthenticationController@reset');
Route::any('passwordreset','AuthenticationController@passwordreset');

Route::group(['before'=>'authenticates'],function(){
    Route::get('users','AuthenticationController@users');
    Route::get('users/add','AuthenticationController@add_User');
    Route::post('users/insert','AuthenticationController@insert');
    Route::get('users/edit/{user_id}','AuthenticationController@edit_User');
    Route::get('users/delete/{user_id}','AuthenticationController@delete_User');
    Route::get('users/usersList','AuthenticationController@usersList');
    Route::post('users/save/{user_id}','AuthenticationController@saveUser');
    Route::post('users/uploadProfilePic','AuthenticationController@uploadProfilePic');
    Route::post('users/api_change_workcenter',array('uses'=>'AuthenticationController@ajax_change_workcenter'));    
});
Route::get('users/download/bulkupdatetemplate','AuthenticationController@getDownload');
Route::post('users/saveusersfromexcel','AuthenticationController@importFileIntoDB');
