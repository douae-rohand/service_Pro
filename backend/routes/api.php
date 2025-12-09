<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/hello', function () {
    return ['message' => 'Bonjour depuis Laravel API'];
});



Route::get('/test', function() {
    return ['message' => 'API Laravel OK'];
});
