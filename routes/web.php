<?php

use App\Http\Controllers\CardController;
use App\Livewire\Pages\Alerts;
use App\Livewire\Pages\AllVariantsForCard;
use App\Livewire\Pages\Bulk;
use App\Livewire\Pages\Cards;
use App\Livewire\Pages\Catalog;
use App\Livewire\Pages\Orders;
use App\Livewire\Pages\PurchaseRecommendation;
use App\Livewire\Pages\Sell;
use App\Livewire\Pages\SetsAndInstances;
use App\Livewire\Pages\SingleVariant;
use App\Livewire\Pages\SingleVariantCard;
use App\Livewire\Pages\SyncSet;
use App\Livewire\Pages\TradableCards;
use App\Livewire\Pages\Wizzard;
use Illuminate\Support\Facades\Route;

Route::get('/', Cards::class);
Route::get('/sets-instances', SetsAndInstances::class);
Route::get('/orders', Orders::class);
Route::get('/purchase-recommendation', PurchaseRecommendation::class);
Route::get('/tradable', TradableCards::class);
Route::get('/bulk', Bulk::class);
Route::get('/sell', Sell::class);
Route::get('/alerts', Alerts::class);
Route::get('/catalog', Catalog::class);
Route::get('/wizzard', Wizzard::class);
Route::get('/sync', SyncSet::class);

Route::get('/all-variants-for-card/{card}', AllVariantsForCard::class);
Route::get('/single-variant-card/{variantCard}', SingleVariantCard::class);
Route::get('/single-variant/{variant}', SingleVariant::class);


Route::get('/card-info', [CardController::class, 'cardInfo']);
Route::get('/order-card', [CardController::class, 'orderCard']);

