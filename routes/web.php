<?php

use App\Http\Controllers\CardController;
use App\Livewire\Bulk;
use App\Livewire\Cards;
use App\Livewire\Orders;
use App\Livewire\PurchaseRecommendation;
use App\Livewire\SetsAndInstances;
use App\Livewire\SingleCard;
use App\Livewire\TradableCards;
use Illuminate\Support\Facades\Route;

Route::get('/', Cards::class);
Route::get('/sets-instances', SetsAndInstances::class);
Route::get('/orders', Orders::class);
Route::get('/purchase-recommendation', PurchaseRecommendation::class);
Route::get('/tradable', TradableCards::class);
Route::get('/bulk', Bulk::class);

Route::get('/card/{card}', SingleCard::class);


Route::get('/card-info', [CardController::class, 'cardInfo']);
Route::get('/order-card', [CardController::class, 'orderCard']);

