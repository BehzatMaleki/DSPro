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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('shift_req','ShiftController@insert_shift_req');
Route::get('shift_req_cancel','ShiftController@del_shift_req');

Route::get('shift_cancel','ShiftController@del_shift');
Route::get('shift_confirm','ShiftController@confirm_shift');

Route::get('user_remove','Auth\RemoveUserController@remove_user');

Route::get('/manage', function(){return view('auth/manage');});