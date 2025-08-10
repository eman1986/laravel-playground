<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('API is Online.')
        ->header('Content-Type', 'text/plain');
});
