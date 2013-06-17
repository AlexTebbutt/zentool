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

Route::get('admin/zendesk/newTickets','ZendeskController@updateTickets');

/*
|--------------------------------------------------------------------------
| Admin Pages
|--------------------------------------------------------------------------
*/
Route::get('admin',function(){
	
	return Redirect::to('admin/dashboard');
	
});

//Dashboard
Route::get('admin/dashboard','AdminController@index'); 

//Organisation report from dashboard
Route::get('admin/organisation/{id}/report','AdminController@postReport');

//Report form
Route::get('admin/report', 'AdminController@getReport');

//Report output
Route::post('admin/report', 'AdminController@postReport');

//Update form
Route::get('admin/update','AdminController@getUpdate');

//Do the update work
Route::post('admin/update','AdminController@postUpdate');

//Show all open tickets
Route::get('admin/report/open','AdminController@getOpenTickets');

//Show all closed tickets
Route::get('admin/report/closed','AdminController@getClosedTickets');

/*
|--------------------------------------------------------------------------
| Organisation Pages
|--------------------------------------------------------------------------
*/
Route::get('organisations', function(){
	
	return Redirect::to('organisations/dashboard');
	
});

//Dashboard
Route::get('organisations/dashboard','OrganisationsController@index'); 

//Report form
Route::get('organisations/report', 'OrganisationsController@getReport');

//Report output
Route::post('organisations/report', 'OrganisationsController@postReport');


/*
|--------------------------------------------------------------------------
| Backend Routes
|--------------------------------------------------------------------------
*/

// Check user loggin credentials
Route::post('login', 'AuthController@postLogin');

Route::get('logout','AuthController@getLogout');

//Zendesk functions
Route::group(array('before' => 'auth'), function()
{

	Route::get('admin/zendesk/updateUsers','ZendeskController@fetchUsers');
	
	Route::get('admin/zendesk/updateOrganisations','ZendeskController@fetchOrganisations');
	
	Route::get('admin/zendesk/updateTickets','ZendeskController@fetchTickets');
	
	Route::get('admin/zendesk/listTicketFields','ZendeskController@listTicketFields');
	
});

//CRON jobs

Route::get('cron/updateTickets','ZendeskController@fetchTickets');

Route::get('cron/updateOrganisations','ZendeskController@fetchOrganisations');

Route::get('cron/updateUsers','ZendeskController@fetchUsers');


?>