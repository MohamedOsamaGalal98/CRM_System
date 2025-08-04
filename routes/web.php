<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// مسار تسجيل الدخول الأساسي (لحل مشكلة Route [login] not defined)
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');
