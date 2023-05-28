<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\user\User_controller;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json([
        "code" => 200,
        "message" => "Welcome to Restful API"
    ]);
});

Route::match(['get', 'post', 'put', 'delete'], '/login', function () {
    return response()->json([
        "code" => 401,
        "message" => "Unable to authenticate user"
    ]);
})->name('login');

Route::middleware(['api'])->group(function () {
    // Please leave a comment because all routes 
    // will be automatically added here when running the command:
    // php artisan make:controller:api $name_controller
    Route::prefix('user')->group(function () {
        Route::post('register', 'App\Http\Controllers\api\user\User_controller@register')->name('user.register');
        Route::post('login', 'App\Http\Controllers\api\user\User_controller@login')->name('user.login');
    });
});

Route::fallback(function () {
    return response()->json([
        "code" => 400,
        "message" => "Can not find method."
    ]);
    
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});