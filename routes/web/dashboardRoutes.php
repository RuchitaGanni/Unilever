<?php 
Route::group(['middleware' => ['auth.custom','LocationCheck']], function() {
    Route::any('dashboard/getProducts','DashboardController@getProducts');
    Route::any('dashboard/getLocations','DashboardController@getLocations');
    Route::any('dashboard/getStorageLocations','DashboardController@getStorageLocations');
});