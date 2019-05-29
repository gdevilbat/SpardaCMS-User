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
	        
			    Route::get('master', 'UserController@index');
			    Route::get('form', 'UserController@create');
			    Route::post('form', 'UserController@store');
			    Route::put('form', 'UserController@store');
			    Route::delete('form', 'UserController@destroy');

			    Route::group(['prefix' => 'api'], function() {
				    Route::get('master', 'UserController@serviceMaster');
			    });
	        
	        /*=====  End of User CMS  ======*/
		});

        
	});
});

