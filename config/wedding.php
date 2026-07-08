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
];
