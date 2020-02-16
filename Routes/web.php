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

Route::group(['prefix' => 'control', 'middleware' => 'core.menu'], function() {
    
	Route::group(['middleware' => 'core.auth'], function() {

		Route::group(['prefix' => 'user'], function() {
	        /*=============================================
	        =            User CMS            =
	        =============================================*/
	        
			    Route::get('master', 'UserController@index')->middleware('can:menu-user')->name('user');
			    Route::get('form', 'UserController@create')->name('user');
			    Route::post('form', 'UserController@store')->middleware('can:create-user')->name('user');
			    Route::put('form', 'UserController@store')->name('user');
			    Route::delete('form', 'UserController@destroy')->name('user');

			    Route::group(['prefix' => 'api'], function() {
				    Route::get('master', 'UserController@serviceMaster')->middleware('can:menu-user');
			    });
	        
	        /*=====  End of User CMS  ======*/
		});

		Route::group(['prefix' => 'group'], function() {
	        /*=============================================
	        =            User CMS            =
	        =============================================*/
	        
			    Route::get('master', 'GroupController@index')->middleware('can:menu-user')->name('group');
			    Route::get('form', 'GroupController@create')->name('group');
			    Route::post('form', 'GroupController@store')->middleware('can:create-user')->name('group');
			    Route::put('form', 'GroupController@store')->name('group');
			    Route::delete('form', 'GroupController@destroy')->name('group');

			    Route::group(['prefix' => 'api'], function() {
				    Route::get('master', 'GroupController@serviceMaster')->middleware('can:menu-user');
			    });
	        
	        /*=====  End of User CMS  ======*/
		});
        
	});
});

