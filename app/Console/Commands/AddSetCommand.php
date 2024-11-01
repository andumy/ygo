<?php

namespace App\Console\Commands;

use App\Enums\AddCardStatuses;
use App\Services\CardService;
use Illuminate\Console\Command;
use function count;
use function explode;

class AddSetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:set
                                    {code : the set base code}
                                    {cards : the cards as structural syntax}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '
        Fast add multiple cards:
        Syntax:
        001x4,002:029,003x2
    ';

    /**
     * Execute the console command.
     */
    public function handle(CardService $cardService)
    {
        $code = $this->argument('code');
        $cards = $this->argument('cards');
        $entries = [];

        $rules = explode(',', $cards);
        foreach ($rules as $rule){
            $entry = explode(':', $rule);
            if (count($entry) == 2) {
                $entries = [
                    ...$entries,
                    ...$this->generateRangeWithLeadingZeros($entry[0], $entry[1])
                ];
                continue;
            }

            $entry = explode('x', $rule);
            if (count($entry) == 2) {
                for ($i= 0; $i < $entry[1]; $i++) {
                    $entries []= $entry[0];
                }
                continue;
            }
            $entries []= $rule;
        }

        $total = 0;
        foreach ($entries as $i) {
            $rarity = null;
            do {
                $response = $cardService->updateCardStock(
                    code: $code . $i,
                    rarity: $rarity,
                    shouldIncrease: true
                );

                if ($response->status === AddCardStatuses::MULTIPLE_OPTIONS) {
                    $rarity = $this->choice(
                        "Select rarity for: " . $response->cardName . '( ' . $code . $i . ' )',
                        $response->rarities,
                        0
                    );
                }

            } while ($response->status === AddCardStatuses::MULTIPLE_OPTIONS);

            switch ($response->status) {
                case AddCardStatuses::NEW_CARD:
                    $this->info('New card added: ' . $response->cardName);
                    $total += $response->cardInstance?->price?->low ?? 0;
                    break;
                case AddCardStatuses::INCREMENT:
                    $this->info($response->cardName . ' incremented');
                    $total += $response->cardInstance?->price?->low ?? 0;
                    break;
                case AddCardStatuses::NOT_FOUND:
                    $this->error($response->cardName. ' not found');
                    break;
                default:
                    throw new \Exception('To be implemented');
            }
        }

        $this->info(count($entries). " cards were added with a total value of: {$total}€");
    }

    function generateRangeWithLeadingZeros($start, $end) {
        // Determine the number of digits based on the length of the input strings
        $length = max(strlen($start), strlen($end));

        // Convert the strings to integers for the range function
        $startInt = intval($start);
        $endInt = intval($end);

        // Generate the range
        $range = range($startInt, $endInt);

        // Format each number with leading zeros
        $formattedRange = array_map(function($number) use ($length) {
            return str_pad($number, $length, '0', STR_PAD_LEFT);
        }, $range);

        return $formattedRange;
    }
}
