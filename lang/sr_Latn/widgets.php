<?php

return [
    'group_views' => [
        'heading' => 'Pregledi po grupama',
        'columns' => [
            'name' => 'Grupa',
            'views_count' => 'Broj pregleda',
        ],
    ],
    'guest_age_chart' => [
        'heading' => 'Distribucija po uzrastu',
        'unknown' => 'Nepoznato',
        'dataset_label' => 'Gosti',
    ],
    'guest_demographics' => [
        'total_guests' => [
            'label' => 'Ukupno gostiju',
            'description' => 'Ukupan broj svih gostiju u bazi',
        ],
        'age_structure' => [
            'label' => 'Struktura po uzrastu',
            'description' => 'Odrasli / Deca / Bebe',
        ],
        'gender_structure' => [
            'label' => 'Struktura po polu',
            'description' => 'Muški / Ženski',
        ],
    ],
    'guest_gender_chart' => [
        'heading' => 'Distribucija po polu',
        'unknown' => 'Nepoznato',
        'dataset_label' => 'Gosti',
    ],
    'guest_status' => [
        'confirmed' => [
            'label' => 'Potvrđeno',
            'description' => 'Gosti koji dolaze',
        ],
        'declined' => [
            'label' => 'Odbijeno',
            'description' => 'Gosti koji ne dolaze',
        ],
        'pending' => [
            'label' => 'Na čekanju',
            'description' => 'Gosti koji se još nisu izjasnili',
        ],
    ],
    'invitation_stats' => [
        'sent_invitations' => [
            'label' => 'Poslate pozivnice',
            'description' => 'Ukupan broj grupa kojima je poslata pozivnica',
        ],
        'total_views' => [
            'label' => 'Ukupno pregleda',
            'description' => 'Koliko puta su sve pozivnice ukupno otvorene',
        ],
    ],
];
