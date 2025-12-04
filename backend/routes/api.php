<?php
use Illuminate\Support\Facades\Route;
Route::get('/users', function () {
    return ['Ahmed', 'Sara', 'Youssef'];
});