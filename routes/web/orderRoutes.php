<?php
Route::group(['middleware' => 'LocationCheck'], function () {
Route::group(['middleware' => 'auth.custom'], function() {
Route::any('production_orders/create','ProductionOrderController@createOrder');
Route::any('getUomProduct/{product_id}','ProductionOrderController@getUom');
});
});
?>