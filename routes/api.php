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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
	return $request->user();
});

// Route::middleware('prevent.browser.access')->group(function () {
    // Define your API routes here


    // Define your API routes here
Route::post('login','API\AuthController@login');
Route::post('register','API\AuthController@register');
// Route::get('get','API\AuthController@getitems');

Route::group(['middleware'=>'auth:api'],function(){


	Route::get('getdata','API\AuthController@getitems');
	Route::put('update/{id}','API\AuthController@updateData');
	Route::get('list','API\AuthController@listItems');
	Route::get('get-data/{id}','API\AuthController@getItems');
	Route::delete('delete/{id}','API\AuthController@deleteItems');


	//CATEGORY
Route::get('category','API\CategoryController@listItems');
Route::post('category/changestatus/{id}','API\CategoryController@changeStatus');
Route::delete('category/{id}','API\Categorycontroller@deleteItems');
Route::post('category/add','API\Categorycontroller@addItems');
Route::put('category/update/{id}','API\Categorycontroller@editCategory');
	
});
// });
