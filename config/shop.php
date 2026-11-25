<?php

return [

    'name' => env('SHOP_NAME', 'Foco Art'),

    'currency' => 'KM',

    // Where new-order notifications go. Leave empty to skip the email.
    'owner_email' => env('SHOP_OWNER_EMAIL'),

    'phone' => env('SHOP_PHONE'),

    'artist' => 'Amer Fočo',
    'artist_genitive' => 'Amera Foče',

    'socials' => [
        'instagram' => 'https://www.instagram.com/foco.art/',
        'facebook' => 'https://www.facebook.com/focooART/',
        'tiktok' => 'https://www.tiktok.com/@foco_art',
    ],

    // Shown for paintings that are not in stock and get painted after the order.
    'made_to_order_time' => env('SHOP_MADE_TO_ORDER_TIME', 'izrada 2–3 sedmice'),

    // Allowed made-to-measure sizes, in cm.
    'commission' => [
        'min' => 20,
        'max' => 200,
    ],

    // Frame colours a buyer can pick. They don't change the price.
    'frames' => [
        'none' => ['label' => 'Bez rama', 'color' => null],
        'black' => ['label' => 'Crni mat', 'color' => '#2b2926'],
        'gold' => ['label' => 'Zlatni', 'color' => '#C9A15E'],
        'silver' => ['label' => 'Srebrni', 'color' => '#C0C0C0'],
        'copper' => ['label' => 'Bakarni', 'color' => '#B87333'],
        'white' => ['label' => 'Bijeli', 'color' => '#F2F2F2'],
        'walnut' => ['label' => 'Orah', 'color' => '#5C3A21'],
    ],

    'order_statuses' => [
        'new' => 'Nova',
        'confirmed' => 'Potvrđena',
        'in_progress' => 'U izradi',
        'shipped' => 'Poslana',
        'completed' => 'Plaćena i završena',
        'cancelled' => 'Otkazana',
    ],
];
