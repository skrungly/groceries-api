<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StatsController;

Route::apiResource('products', ProductController::class);
Route::apiResource('items', ItemController::class);
Route::apiResource('stats', StatsController::class);
