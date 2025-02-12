<?php

use App\Http\Controllers\AlatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PelangganDataController;
use App\Http\Controllers\PenyewaanController;
use App\Http\Controllers\PenyewaanDetailController;
use App\Http\Middleware\VerifyAccessToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(AuthController::class)->prefix('access')->group(function(){
    Route::post('/login',  'login');
    Route::post('/register','register');
    Route::post('/refresh-token', 'refresh');
    Route::post('/password-reset/request','requestReset');
    Route::post('/password-reset','resetPassword');
});
    Route::post('/admin/login', [AdminController::class, 'login']);
    Route::post('/admin/logout', [AdminController::class, 'logout']);

//Verify token saya gunakan untuk mengecek jika yang hanya bisa dimasukkan adalah accesstoken bukan refresh token

Route::middleware(['auth:api',VerifyAccessToken::class])->group(function(){
    Route::post('/logout',[AuthController::class,'logout']);
    Route::apiResource('/alat',AlatController::class);
    Route::apiResource('/kategori',KategoriController::class);
    Route::apiResource('/pelanggan',PelangganController::class);
    Route::apiResource('/data-pelanggan',PelangganDataController::class);
    Route::apiResource('/penyewaan',PenyewaanController::class);
    Route::apiResource('/penyewaan-detail',PenyewaanDetailController::class);
});

