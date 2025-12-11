<?php

use App\Http\Controllers\API\ApiController;
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
Route::post('/shop-login', [ApiController::class, 'shopLogin']);
Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/get-users', [ApiController::class, 'getUsers']);
    Route::post('/sync-configrations', [ApiController::class, 'configrations']);
    Route::post('/refresh-token', [ApiController::class, 'refreshToken']);
    Route::get('/pos-login/{token}', [ApiController::class, 'tokenData']);
    Route::get('/credentials/{id}', [ApiController::class, 'showData']);

    Route::post('/get-shops', [ApiController::class, 'getShops']);
    Route::post('/get-products', [ApiController::class, 'getProducts']);
    Route::post('/get-product-by-id', [ApiController::class, 'getProductById']);
    Route::post('/synced-products', [ApiController::class, 'syncedProducts']);
    Route::post('/sync-orders', [ApiController::class, 'syncOrdersApi']);
    Route::post('/get-customer', [ApiController::class, 'getCustomer']);
    Route::post('/get-custom-discounts', [ApiController::class, 'getCustomDiscounts']);
});
