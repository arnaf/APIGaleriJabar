<?php

use Illuminate\Support\Facades\Route;


        

Route::group(
    [
        'middleware'    => 'auth:api',
        'prefix'        => 'auth',
    ],
    function() {
        Route::post('/logout', 'AuthController@logout');
    }
);
