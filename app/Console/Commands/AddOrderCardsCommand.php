<?php

namespace App\Console\Commands;

use App\Enums\AddCardStatuses;
use App\Repositories\OrderRepository;
use App\Services\CardService;
use Illuminate\Console\Command;

class AddOrderCardsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:order-cards
                                {order : the order name}
                                {cards* : the cards code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(CardService $cardService, OrderRepository $orderRepository)
    {
        $orderName = $this->argument('order');
        $order = $orderRepository->findByName($orderName);
        if(!$order){
            $this->error('Order not found');
            return;
        }
        $cards = $this->argument('cards');

        foreach ($cards as $card) {
            $rarity = null;
            do {
                $response = $cardService->updateCardStock(
                    code: $card,
                    rarity: $rarity,
                    orderId: $order->id,
                    shouldIncrease: true
                );

                if ($response->status === AddCardStatuses::MULTIPLE_OPTIONS) {
                    $rarity = $this->choice(
                        "Select rarity for: " . $response->cardName . '( ' . $card . ' )',
                        $response->rarities,
                        0
                    );
                }

            } while ($response->status === AddCardStatuses::MULTIPLE_OPTIONS);

            switch ($response->status) {
                case AddCardStatuses::NEW_CARD:
                    $this->info('New card added: ' . $response->cardName);
                    break;
                case AddCardStatuses::INCREMENT:
                    $this->info($response->cardName . ' incremented');
                    break;
                case AddCardStatuses::NOT_FOUND:
                    $this->error($response->cardName. ' not found');
                    break;
                case AddCardStatuses::PART_OF_ANOTHER_ORDER:
                    $this->error($response->cardName. ' ('.$card.') is part of another order');
                    break;
                default:
                    throw new \Exception('To be implemented');
            }
        }
    }
}
