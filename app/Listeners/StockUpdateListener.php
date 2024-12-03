<?php

namespace App\Listeners;

use App\Events\StockUpdateEvent;
use App\Repositories\SetRepository;

class StockUpdateListener
{
    /**
     * Create the event listener.
     */
    public function __construct(private SetRepository $setRepository)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(StockUpdateEvent $event): void
    {
        $this->setRepository->updateStock($event->set, $event->stockState);
    }
}
