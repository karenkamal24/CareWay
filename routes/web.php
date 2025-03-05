<?php
use App\Models\Doctor;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test-env', function () {
    return env('PAYMOB_API_KEY', 'Not Found');
});
