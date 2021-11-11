<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->namespace('App\Http\Controllers')->group(function() {
    //Todo Routes
    Route::prefix('todo')->middleware('auth:sanctum')->group(function() {
        Route::get('/', 'TodoController@index')->name('todo.index');
        Route::post('/', 'TodoController@create')->name('todo.create');
        Route::get('/{id}', 'TodoController@show')->name('todo.show')->where('id', '[0-9]+');
        Route::put('/{id}', 'TodoController@update')->name('todo.update')->where('id', '[0-9]+');
        Route::delete('/{id}', 'TodoController@destroy')->name('todo.delete')->where('id', '[0-9]+');
    });
    
    //User Route
    Route::prefix('users')->group(function() {
        Route::post('/', 'AuthController@store')->name('users.store');
        Route::post('/login', 'AuthController@login')->name('users.login');
        Route::get('/logout', 'AuthController@logout')->middleware('auth:sanctum')->name('users.logout');
    });
});

Route::get('/unauthenticate', function() {
    return response()->json(['error' => 'Usuário não logado'], 401);
})->name('login');
