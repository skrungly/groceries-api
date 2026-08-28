<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ItemController;

Route::apiResource('products', ProductController::class);
Route::apiResource('items', ItemController::class);
