<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/* @var \Laravel\Lumen\Routing\Router $router */
$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => 'auth'], function ($router) {
    $router->get('/list/{listName}', 'SearchesController@get');
    $router->post('/list/{listName}/add', 'SearchesController@addSearch');
    $router->get('/list/{listName}/{searchId}', 'SearchesController@getSearch');
    $router->delete('/list/{listName}/{searchId}', 'SearchesController@removeSearch');
});
