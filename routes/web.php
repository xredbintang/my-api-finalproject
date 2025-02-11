<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\productNotController;
use App\Http\Controllers\redisController;
use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return view('welcome');
});
