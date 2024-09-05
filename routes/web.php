<?php

use App\Livewire\Cards;
use App\Livewire\SetsAndInstances;
use Illuminate\Support\Facades\Route;

Route::get('/', Cards::class);
Route::get('/sets-instances', SetsAndInstances::class);
