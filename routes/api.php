<?php

use App\Http\Controllers\DataControllerA;
use App\Http\Controllers\DataControllerB;
use App\Http\Controllers\DataControllerC;
use Illuminate\Support\Facades\Route;

Route::get('/data-a', [DataControllerA::class, 'getData']);

Route::get('/data-b', [DataControllerB::class, 'getData']);

Route::get('/data-c', [DataControllerC::class, 'getData']);
