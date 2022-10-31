<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
// require_once('includes/auth.php');
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', 'AuthController@register');
Route::post('/login', 'AuthController@login');

require_once('includes/auth.php');
require_once('includes/profile.php');
require_once('includes/user.php');
Route::group(
    ['middleware' => 'auth:api'],
    function() {
        require_once('includes/user.php');
        require_once('includes/profile.php');

    }
);
