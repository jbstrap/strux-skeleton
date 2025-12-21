<?php

use App\Domain\Identity\Event\UserRegistered;
use App\Domain\Identity\Listener\SendWelcomeEmail;

return [
    /*
    |--------------------------------------------------------------------------
    | Event Listener
    |--------------------------------------------------------------------------
    |
    | The event listener mappings for the application. This array maps your
    | event classes to the listener classes that should be called when
    | that event is dispatched.
    |
    */
    'events' => [
        'listeners' => [
            // UserRegistered::class => [
            //    SendWelcomeEmail::class,
                // You can add more listeners for the same event here
                // \App\Listener\AwardWelcomeBonus::class,
            // ],

            // \App\Event\OrderPlaced::class => [
            //     \App\Listener\SendOrderConfirmation::class,
            //     \App\Listener\UpdateInventory::class,
            // ]
        ]
    ]
];