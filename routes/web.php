<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/admin');
});

// Laravel/Filament بيدور على route اسمه login
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');
