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

Route::group(['middleware' => ['web']], function () {
    Route::match(['get'], '/', 'ReservationController@index')->name('reservation_index');
    Route::match(['get'], '/settings/{code?}', 'ReservationController@getSettings')->name('reservation_settings');
    Route::match(['get'], '/seats/{code}', 'ReservationController@getSeats')->name('reservation_seats');
    Route::match(['post'], '/reserve', 'ReservationController@reserve')->name('reservation_reserve');
    Route::match(['post'], '/revoke', 'ReservationController@revoke')->name('reservation_revoke');
    Route::match(['post'], '/order', 'ReservationController@order')->name('reservation_order');
});