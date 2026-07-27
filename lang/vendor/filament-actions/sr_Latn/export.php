<?php

return [
    'label' => 'Izvezi :label',

    'modal' => [
        'heading' => 'Izvoz :label',
        'form' => [
            'columns' => [
                'label' => 'Kolone',
                'actions' => [
                    'select_all' => [
                        'label' => 'Izaberi sve',
                    ],

                    'deselect_all' => [
                        'label' => 'Poništi izbor svega',
                    ],
                ],

                'form' => [
                    'is_enabled' => [
                        'label' => ':column je omogućena',
                    ],

                    'label' => [
                        'label' => 'Oznaka za :column',
                    ],
                ],
            ],
        ],

        'actions' => [
            'export' => [
                'label' => 'Izvezi',
            ],
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Izvoz je završen',
            'actions' => [
                'download_csv' => [
                    'label' => 'Preuzmi .csv',
                ],
                'download_xlsx' => [
                    'label' => 'Preuzmi .xlsx',
                ],
            ],
        ],

        'max_rows' => [
            'title' => 'Izvoz je prevelik',
            'body' => 'Ne možete izvesti više od 1 reda odjednom.|Ne možete izvesti više od :count redova odjednom.',
        ],

        'no_columns' => [
            'title' => 'Nisu izabrane kolone',
            'body' => 'Izaberite najmanje jednu kolonu za izvoz.',
        ],

        'started' => [
            'title' => 'Izvoz je pokrenut',
            'body' => 'Izvoz je pokrenut i 1 red će biti obrađen u pozadini. Dobićete obaveštenje sa linkom za preuzimanje kada bude završen.|Izvoz je pokrenut i :count redova će biti obrađeno u pozadini. Dobićete obaveštenje sa linkom za preuzimanje kada bude završen.',
        ],
    ],
    'file_name' => 'export-:export_id-:model',
];
