<?php

namespace App\Console\Commands;

use App\Enums\AddCardStatuses;
use App\Models\CardInstance;
use App\Repositories\CardInstanceRepository;
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
    public function handle(
        CardService $cardService,
        OrderRepository $orderRepository,
        CardInstanceRepository $cardInstanceRepository
    )
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
                if(!$rarity){
                    $option = null;
                } else {
                    $option = $cardInstanceRepository->findBySetCodeAndRarity($card, $rarity);
                }

                $response = $cardService->updateCardStock(
                    code: $card,
                    option: $option,
                    orderId: $order->id,
                    shouldIncrease: true
                );

                if ($response->status === AddCardStatuses::MULTIPLE_OPTIONS) {
                    $rarity = explode(" #",
                        $this->choice(
                            "Select rarity for: " . $response->options->first()->card->name . '( ' . $card . ' )',
                            $response->options->map(fn(CardInstance $option) => $option->rarity_verbose. " #". $option->card->ygo_id)->toArray(),
                            0
                        )
                    )[0];
                }

            } while ($response->status === AddCardStatuses::MULTIPLE_OPTIONS);

            switch ($response->status) {
                case AddCardStatuses::NEW_CARD:
                    $this->info('New card added: ' . $response->options->first()->card->name);
                    break;
                case AddCardStatuses::INCREMENT:
                    $this->info($response->options->first()->card->name . ' incremented');
                    break;
                case AddCardStatuses::NOT_FOUND:
                    $this->error($response->options->first()->card->name. ' not found');
                    break;
                case AddCardStatuses::PART_OF_ANOTHER_ORDER:
                    $this->error($response->options->first()->card->name. ' ('.$card.') is part of another order');
                    break;
                default:
                    throw new \Exception('To be implemented');
            }
        }
    }
}
