<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Invitation Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for the wedding invitation.
    | These settings control how the countdown and dates are formatted
    | on the invitation page.
    |
    */
    'invitation' => [
        'countdown' => [
            'wedding_format' => 'd.m.Y',
            'rsvp_format' => 'd.m.Y H:i',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines settings for the various widgets used throughout
    | the application, such as the countdown widget labels and formats.
    |
    */
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
