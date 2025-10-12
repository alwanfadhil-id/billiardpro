<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\TransactionsController;
use App\Http\Controllers\Api\UsersController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes for tables
Route::apiResource('tables', TableController::class);
Route::get('tables/statuses', [TableController::class, 'getStatuses']);

Route::apiResource('products', ProductsController::class);
Route::apiResource('transactions', TransactionsController::class);
Route::apiResource('users', UsersController::class);