<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Exports\UserExport;
use App\Filament\Exports\UserTemplateExport;
use App\Filament\Imports\UserImport;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return view('welcome');
});

// مسار تسجيل الدخول الأساسي (لحل مشكلة Route [login] not defined)
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// Test routes for export functionality
Route::get('/test-export', function () {
    return Excel::download(new UserExport(), 'users-test.xlsx');
});

Route::get('/test-template', function () {
    return Excel::download(new UserTemplateExport(), 'users-template-test.xlsx');
});

Route::get('/test-boolean', function () {
    $import = new UserImport();
    
    // Test different values
    $testValues = [
        'Yes' => $import->getBooleanValue('Yes'),
        'No' => $import->getBooleanValue('No'), 
        'yes' => $import->getBooleanValue('yes'),
        'no' => $import->getBooleanValue('no'),
        '1' => $import->getBooleanValue('1'),
        '0' => $import->getBooleanValue('0'),
        1 => $import->getBooleanValue(1),
        0 => $import->getBooleanValue(0),
    ];
    
    return response()->json($testValues);
});
