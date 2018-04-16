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

Route::get('/', 'ParserController@index');
Route::post('/url-action', 'ParserController@createDom');
Route::get('/url/{id}', 'TableController@getAllUrlTable');
Route::get('/table/{id}', 'TableController@getTable');
Route::get('/parse-sql', 'ParserController@sql_parser');
Route::get('/table/{id}/delete', 'TableController@delete_table');
Route::get('/column/{id}/delete', 'TableController@delete_column');