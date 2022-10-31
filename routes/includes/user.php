<?php

use Illuminate\Support\Facades\Route;

    Route::get('/user', 'UserController@indexAll');
    Route::get('/user/{id}', 'UserController@showID');
    Route::post('/user', 'UserController@store');
    Route::post('/user/{id}', 'UserController@update');
    Route::delete('/user/{id}', 'UserController@destroy');
