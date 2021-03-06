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




Route::get('/paypal',['uses' => 'PaypalController@index', 'as' => 'paypal.index']);
Route::get('/paypal/ExecutePayment',['uses' => 'PaypalController@create', 'as' => 'paypal.ExecutePayment']);

// Plans Routes
Route::get('/paypal/CreatePlan',['uses' => 'PlanController@createPlan', 'as' => 'paypal.CreatePlan']);
Route::get('/paypal/UpdatePlan',['uses' => 'PlanController@updatePlan', 'as' => 'paypal.UpdatePlan']);
Route::get('/paypal/DeletePlan/{id?}',['uses' => 'PlanController@deletePlan', 'as' => 'paypal.DeletePlan']);

Route::get('/paypal/PlanDetail/{id}',['uses' => 'PlanController@getPlan', 'as' => 'paypal.PlanDetail']);
Route::get('/paypal/PlanList',['uses' => 'PlanController@getPlanList', 'as' => 'paypal.PlanList']);
