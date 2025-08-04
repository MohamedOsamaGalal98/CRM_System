<?php

use App\Filament\Imports\UserImport;

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
