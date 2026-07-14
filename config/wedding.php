<?php

return [
    'widgets' => [

        'countdown' => [
            'wedding_format' => 'd.m.Y',
            'rsvp_format' => 'd.m.Y H:i',

            'label' => [
                'wedding_past' => 'Venčanje je prošlo',
                'wedding_until' => 'Do venčanja',
                'application_past' => 'Prijave su zatvorene',
                'application_until' => 'Prijave otvorene još',
            ],
        ],
    ],

    'schedules' => [
        ['name' => 'Crkva', 'enabled' => true],
        ['name' => 'Opština', 'enabled' => true],
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta (Open Graph) Defaults
    |--------------------------------------------------------------------------
    |
    | Default values used when generating meta tags for the invitation link
    | preview. These are used as placeholders in the admin and as fallbacks
    | on the front-end when no custom values are defined.
    |
    */
    'meta' => [
        'title' => 'wedding.title',
        'description' => 'wedding.greeting',
        'image' => null,
    ],
];
