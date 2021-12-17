<?php
//views
Route::get('delivery/createStoEcc', 'DeliveryController@createStoEcc');
Route::get('deliveries/getPdfDetailsForTp/{docNumber}', 'DeliveryController@getPdfDetailsForTp');
Route::get('deliveries/downloadTPPDF/{docNumber}', 'DeliveryController@TPPdfDownload');
/*dispatch and grn report routes */
Route::any('delivery/dispatchReport','DeliveryController@dispatchReport');
Route::any('delivery/dispatchReport2','DeliveryController@dispatchReport2');
Route::any('delivery/grnReport','DeliveryController@grnReport');
Route::any('delivery/getPutaway','DeliveryController@getPutaway');
Route::any('delivery/getIOT/{tp_id}/{document_no}','DeliveryController@getIOT');
Route::any('delivery/getGrnIOT/{document_no}','DeliveryController@getGrnIOT');

/*ends here*/
Route::any('conversionImport','DeliveryController@conversionImport');
Route::any('skuImport','DeliveryController@skuImport');
Route::any('productLocationsimport','DeliveryController@productLocationsimport');
Route::group(['middleware' => 'LocationCheck'], function () {
Route::get('delivery/getElementdata/{arg}/{id}', 'DeliveryController@getElementdata');
Route::get('delivery/matBatch/{p_id}','DeliveryController@matBatch');
Route::group(['middleware' => 'auth.custom'], function()
{
Route::get('deliveries/add','DeliveryController@add');
Route::post('delivery/save','DeliveryController@save');
//views end

//Route::get('delivery/getElementdataNew', 'DeliveryController@getElementdata');

Route::post('delivery/createDeliveryOrders', 'DeliveryController@createDeliveryOrders');

Route::get('delivery/delwithProduct/{Id}/{product_id}', 'DeliveryController@delwithProduct');

Route::get('delivery/editDeliveryDetails/{delivery_id}/{product_id}', 'DeliveryController@editDeliveryDetails');

Route::put('delivery/updateDeliveryDetails/{delivery_id}/{product_id}', 'DeliveryController@updateDeliveryDetails');

Route::post('delivery/savedeliveryfromexcel', 'DeliveryController@savedeliveryfromexcel');

//Route::get('delivery/{arg}','DeliveryController@index');
Route::any('delivery/getDeliveries/{id}', 'DeliveryController@getDeliveries');

Route::get('delivery/getElement	/{id}', 'DeliveryController@getElementdata');
Route::get('delivery/getElementdata/{arg}', 'DeliveryController@getElementdata');

Route::get('delivery/{arg}','DeliveryController@index');
Route::get('getPlantStorageLoc/{plantCode}','DeliveryController@getPlantStrLocation');
Route::get('getConversion/{qty}/{UOM}/{p_id}','DeliveryController@getConversion');
});
});
?>