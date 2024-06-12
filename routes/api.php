<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrdersController;
use App\Http\Controllers\API\BannerController;
use App\Http\Controller\API\DashboardController;
use App\Http\Controller\API\CountryController;


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
Route::post('login','API\AuthController@login')->name('login');
Route::post('register','API\AuthController@register');
// Route::get('get','API\AuthController@getitems');

Route::group(['middleware'=>'auth:api'],function(){

   //Dashboard
   Route::get('getcount','API\DashboardController@getCounts');


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
	Route::get('category/{id}','API\Categorycontroller@getItems');
	Route::post('category/update/{id}','API\Categorycontroller@editCategory');
    //END CATEGORY

	//PRODUCT
	Route::post('product/add','API\ProductController@addItems');
	Route::get('product','API\ProductController@listItems');
	Route::post('product/changestatus/{id}','API\ProductController@changeStatus');
	Route::delete('product/{id}','API\ProductController@deleteItems');
	Route::get('product/{id}','API\ProductController@getItems');
	Route::post('product/update/{id}','API\ProductController@updateItems');
	//END PRODUCT

	//Orders
	Route::get('orders','API\OrdersController@listItems');
	Route::get('order-status-counts','API\OrdersController@getOrderStatusCounts');



	//Banners
	Route::post('banner/add','API\BannerController@addItems')->name('banner.add');
	Route::get('banners','API\BannerController@listItems')->name('banners');



	//Country
	Route::get('country','API\CountryController@listItems');
	Route::post('country/add','API\CountryController@addItems')->name('country.add');
	Route::post('country/update/{id}','API\CountryController@updateItems')->name('country.update');
	Route::get('country/{id}', 'API\CountryController@getItems');
	Route::post('country/changestatus/{id}','API\CountryController@changeStatus');
	Route::delete('country/{id}','API\CountryController@deleteItems');

	
});

