<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrdersController;
use App\Http\Controllers\API\BannerController;
use App\Http\Controller\API\DashboardController;
use App\Http\Controller\API\CountryController;
use App\Http\Controller\API\AuthController;
use App\Http\Controller\API\CmspagesController;


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

// Route::middleware('prevent.browser.access')->group(function () {
    // Define your API routes here
	Route::fallback(function () {
		return response()->json(['message' => 'Resource not found.'], 404);
	});

    // Define your API routes here
Route::post('login','API\AuthController@login')->name('login');
Route::post('register','API\AuthController@register');
Route::post('forget-password', 'API\AuthController@forgetPassword');
Route::post('password/reset', 'API\AuthController@resetPassword')->name('password.reset');
Route::get('cms/all','API\CmspagesController@index'); // Fetch all CMS pages
Route::get('cms/{slug}', 'API\CmspagesController@show'); // Fetch a single CMS page by slug

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
	Route::get('banners/{id}','API\BannerController@getItems');
	Route::post('banners/add','API\BannerController@addItems')->name('banner.add');
	Route::get('banners','API\BannerController@listItems')->name('banners');
	Route::post('banners/update/{id}', 'API\BannerController@updateItems');
	Route::post('banners/changestatus/{id}','API\BannerController@changeStatus');
	Route::delete('banners/{id}','API\BannerController@deleteItems');




	//Country
	Route::get('country','API\CountryController@listItems');
	Route::post('country/add','API\CountryController@addItems')->name('country.add');
	Route::post('country/update/{id}','API\CountryController@updateItems')->name('country.update');
	Route::get('country/{id}', 'API\CountryController@getItems');
	Route::post('country/changestatus/{id}','API\CountryController@changeStatus');
	Route::delete('country/{id}','API\CountryController@deleteItems');

	//Cms Page
	Route::post('cms/add','API\CmspagesController@addItems');
	Route::get('cms','API\CmspagesController@listItems');
	Route::get('cms/get/{id}','API\CmspagesController@getItems');
	Route::post('cms/changestatus/{id}','API\CmspagesController@changeStatus');
	Route::delete('cms/delete/{id}','API\CmspagesController@deleteItems');
	Route::post('cms/update/{id}','API\CmspagesController@updateItems');
	

	
});

