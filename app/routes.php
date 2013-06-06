<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/


/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/

//Determine where if a user is logged in and if so where they need to be
Route::get('/', 'SiteController@index');

Route::get('login','AuthController@index');

/* Uncomment once admin panel is built for the time being fgo straight to the report */
//Route::get('admin','AdminController@getIndex');
Route::get('admin',function(){
	
	return Redirect::to('admin/report');
	
});

//Route::get('organisations', 'OrganisationsController@index');

Route::get('organisations', function(){
	
	return Redirect::to('organisations/report');
	
});

Route::get('organisations/report', 'OrganisationsController@getReport');

Route::post('organisations/report', 'OrganisationsController@postReport');

//Show super admin page
//Route::resource('admin','AdminController@index');

//Show super admin report page

//Show organisation report page
Route::get('admin/report', 'AdminController@getReport');

Route::post('admin/report', 'AdminController@postReport');

//Show tickets for an organisation
Route::get('organisations/{id}/tickets', 'TicketsController@showTicketsByOrg');

//Show organisation account summary page

//Show organisation account history page

/*
|--------------------------------------------------------------------------
| Backend Routes
|--------------------------------------------------------------------------
*/

// Check user loggin credentials
Route::post('login', 'AuthController@postLogin');

Route::get('logout','AuthController@getLogout');

?>