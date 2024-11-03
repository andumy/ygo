<?php

return [
    'cards' => env('YGO_API_URL'),
    'sets' => env('YGO_API_SETS_URL'),
    'image_url' => env('YGO_IMAGE_URL'),
    'pedia_url' => env('YGO_PEDIA_URL'),
    'price' => [
        'sets' => env('YGO_PRICE_SETS_URL'),
        'cards_in_set' => env('YGO_PRICE_SET_DATA_URL'),
        'parser' => env('YGO_PRICE_PARSER_URL'),
    ],
];
