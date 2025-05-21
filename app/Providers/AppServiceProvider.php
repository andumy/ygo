<?php

namespace App\Providers;

use App\Synths\CatalogMatchSynth;
use Illuminate\Support\ServiceProvider;
use Generator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /**
         * Get the JSON decoded body of the response as a lazyCollection. The pointer is optional, and should follow the
         * JSON Pointer RFC 6901 syntax. See link below for more information on how to use.
         *
         * @link https://github.com/halaxa/json-machine?tab=readme-ov-file#json-pointer
         */
        Response::macro('lazy', fn (?string $key = null): LazyCollection => new LazyCollection(function () use ($key): Generator {
            $options = [
                'decoder' => new ExtJsonDecoder(true), // Cast objects to associative arrays
                'pointer' => $key ?? '',
            ];

            /** @var Response $this */
            rewind($resource = $this->resource());
            foreach (Items::fromStream($resource, $options) as $arrayKey => $item) {
                yield $arrayKey => $item;
            }
        }));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::propertySynthesizer(CatalogMatchSynth::class);
    }
}
