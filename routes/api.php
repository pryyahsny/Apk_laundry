<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', 'Auth\LoginController@login');

//ROUTE INI HANYA BISA DIAKSES APABILA request YANG TERIMA MEMILIKI TOKEN YAG VALID
Route::group(['middleware' => 'auth:api'], function() {
    Route::resource('/outlets', 'API\OutletController')->except(['show']);

    Route::resource('/couriers', 'API\UserController')->except(['create', 'show', 'update']);
    Route::post('/couriers/{id}', 'API\UserController@update')->name('couriers.update');

    Route::resource('product', 'API\ProductController')->except(['create', 'show']);

    Route::get('/product/laundry-type', 'API\ProductController@getLaundryType');
    Route::post('/product/laundry-type', 'API\ProductController@storeLaundryType');

    Route::get('roles', 'API\RolePermissionController@getAllRole')->name('roles');
    Route::get('permissions', 'API\RolePermissionController@getAllPermission')->name('permission');
    Route::post('role-permission', 'API\RolePermissionController@getRolePermission')->name('role_permission');
    Route::post('set-role-permission', 'API\RolePermissionController@setRolePermission')->name('set_role_permission');
    Route::post('set-role-user', 'API\RolePermissionController@setRoleUser')->name('user.set_role');

    Route::get('user-authenticated', 'API\UserController@getUserLogin')->name('user.authenticated');
    Route::get('user-lists', 'API\UserController@userLists')->name('user.index');

    Route::resource('expenses', 'API\ExpensesController')->except(['create', 'show']);

    Route::resource('customer', 'API\CustomerController')->except(['create', 'show']);

    Route::resource('transaction', 'API\TransactionController')->except(['create', 'show']);

    Route::post('transaction/complete-item', 'API\TransactionController@completeItem');

    Route::post('transaction/payment', 'API\TransactionController@makePayment');

    Route::get('chart', 'API\DashboardController@chart');

    Route::get('export', 'API\DashboardController@exportData');
});
