<?php
Route::group(['middleware' => 'LocationCheck'], function () {
Route::group(['middleware' => 'auth.custom'], function() {
Route::get('importPoList_old', 'ImportPOController@importPOList');
Route::get('importPodata', 'ImportPOController@importPODataList');
Route::get('getTreeImportPoList/{manufacturer_id}', 'ImportPOController@getTreeImportPoList');

Route::get('GrnCanToConfirm/{grnCanDocNo}', 'ImportPOController@grnCanToConfirmation');

Route::get('importPoList', 'ImportPOController@importPOList_new');

});
Route::get('deleteGrn/{grnNumber}', 'ImportPOController@deleteGrn');
Route::get('getTreeImportPoList_new', 'ImportPOController@getTreeImportPoList_new');
});
?>
