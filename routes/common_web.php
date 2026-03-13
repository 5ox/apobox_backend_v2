<?php

use Illuminate\Support\Facades\Route;

// Health check for Railway (no auth, no middleware)
Route::get('/health', fn () => response('ok', 200));
