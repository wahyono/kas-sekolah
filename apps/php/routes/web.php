<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('app');
});

// Swagger API Documentation
Route::get('/api/docs', function () {
    return view('swagger');
})->name('api.docs');

Route::get('/docs', function () {
    return redirect('/api/docs');
});

Route::get('/swagger', function () {
    return redirect('/api/docs');
});

// cPanel Maintenance Helpers (No SSH needed)
Route::get('/symlink', function () {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    return response()->json(['message' => 'Storage symlink created successfully!']);
});

// User Guide Documentation
Route::get('/user-guide', function () {
    return view('user_guide');
})->name('user.guide');

Route::get('/guide', function () {
    return redirect('/user-guide');
});



