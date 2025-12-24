<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', function () {
    return view('welcome');
});

// Broadcasting authentication route
Route::post('/broadcasting/auth', function () {
    return Broadcast::auth(request());
})->middleware('web');

// Serve profile photos from storage
Route::get('/storage/profiles/{filename}', function ($filename) {
    \Log::info('Profile photo route hit', ['filename' => $filename]);
    
    $path = storage_path('app/public/profiles/' . $filename);
    \Log::info('Looking for file at path', ['path' => $path]);
    \Log::info('File exists', ['exists' => file_exists($path)]);
    
    if (!file_exists($path)) {
        \Log::error('Profile photo not found', ['path' => $path]);
        abort(404);
    }
    
    $file = file_get_contents($path);
    $mimeType = mime_content_type($path);
    
    \Log::info('Serving profile photo', ['mime_type' => $mimeType, 'size' => strlen($file)]);
    
    return response($file, 200, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'inline; filename="' . $filename . '"'
    ]);
})->where('filename', '.*');
