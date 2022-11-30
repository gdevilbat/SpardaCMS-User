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

Route::group(['prefix' => 'control', 'middleware' => 'core.auth'], function() {
    
	Route::group(['prefix' => 'user'], function() {

		Route::group(['middleware' => 'core.menu'], function() {
	        /*=============================================
	        =            User CMS            =
	        =============================================*/
	        
			    Route::get('master', 'UserController@index')->middleware('can:menu-user')->name('user');
			    Route::get('form', 'UserController@create')->name('user');
			    Route::post('form', 'UserController@store')->middleware('can:create-user')->name('user');
			    Route::put('form', 'UserController@store')->name('user');
			    Route::delete('form', 'UserController@destroy')->name('user');

	        
	        /*=====  End of User CMS  ======*/
		});

		Route::post('show', 'UserController@show');
		Route::post('data', 'UserController@data')->middleware('can:menu-user');

	    Route::group(['prefix' => 'api'], function() {
		    Route::get('master', 'UserController@serviceMaster')->middleware('can:menu-user');
	    });

	});

	Route::group(['prefix' => 'group'], function() {

		Route::group(['middleware' => 'core.menu'], function() {
	        /*=============================================
	        =            User CMS            =
	        =============================================*/
	        
			    Route::get('master', 'GroupController@index')->middleware('can:group-user')->name('cms.group.master');
			    Route::get('form', 'GroupController@create')->name('cms.group.create');
			    Route::post('form', 'GroupController@store')->middleware('can:group-user')->name('group');
			    Route::put('form', 'GroupController@store')->name('group');
			    Route::post('group-scope', 'GroupController@accessScope')->middleware('can:group-user')->name('cms.group-scope.store');
			    Route::delete('form', 'GroupController@destroy')->name('group');

	        
	        /*=====  End of User CMS  ======*/
		});

	    Route::group(['prefix' => 'api'], function() {
		    Route::get('master', 'GroupController@serviceMaster')->middleware('can:group-user');
	    });
	    
	});
});

