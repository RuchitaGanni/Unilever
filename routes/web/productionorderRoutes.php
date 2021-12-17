<?php
/*to load orders*/
Route::any('production_orders_report','ProductionOrderController@getOrders_reports');
/*ends here*/
/*search po */
Route::any('productorder/getPOorders_report/{p_id}/{l_id}', 'ProductionOrderController@getPOorders_report');
/*ends here*/
Route::any('reports/iotgrid/{order_no}','ProductionOrderController@iot_data'); 
Route::group(['middleware' => 'LocationCheck'], function () {
Route::group(['middleware' => 'auth.custom'], function() {
Route::any('production_orders','ProductionOrderController@getOrders')->name('profile');;
Route::any('productorder/getPOorders/{p_id}/{l_id}', 'ProductionOrderController@getPOorders');

Route::any('productorder/createOrder', 'ProductionOrderController@createOrder');
Route::any('productorder/getPoQuantity', 'ProductionOrderController@getPoQuantity');

Route::any('productorder/getPOconfirmdetails/{erp_doc_no}/{eseal_doc_no}', 'ProductionOrderController@getPOconfirmdetails');
Route::post('productorder/cancelOrder', 'ProductionOrderController@cancelOrder');

Route::post('productorder/getECCstatus', 'ProductionOrderController@getECCstatus');
Route::post('productorder/getConversion/{qty}/{UOM}/{p_id}', 'ProductionOrderController@getConversion');
// /*to load orders*/
// Route::any('production_orders_report','ProductionOrderController@getOrders_reports');
// /*ends here*/
// /*search po */
// Route::any('productorder/getPOorders_report/{p_id}/{l_id}', 'ProductionOrderController@getPOorders_report');
// /*ends here*/
// Route::any('reports/iotgrid/{order_no}','ProductionOrderController@iot_data');
});
});