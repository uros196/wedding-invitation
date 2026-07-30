<?php

return [
    'title' => 'Pozivnica za venčanje',
    'greeting' => 'Pozvani ste na naše venčanje. Pridružite nam se u proslavi ljubavi!',

    'manage_wedding' => [
        'main_image_description' => 'Ova slika se prikazuje na vrhu pozivnice.',
        'meta' => [
            'description' => 'Koristi se za generisanje pregleda linka kada se pozivnica podeli.',
            'image_fallback' => 'Opciono. Ako se ostavi prazno, koristiće se glavna slika iznad.',
        ],
        'timeline' => [
            'not_defined' => 'Vremenska linija još uvek nije definisana.',
        ],
    ],

    'groups' => [
        'form' => [
            'basic_info_description' => 'Unesite naziv grupe i personalizovanu poruku.',
            'name_placeholder' => 'npr. Petrović Family',
        ],
        'meta' => [
            'description' => 'Personalizujte pregled linka za ovu grupu. Ako se ostavi prazno, koristiće se meta podaci venčanja.',
            'fallback_description' => 'Opciono. Ako se ostavi prazno, koristiće se meta podaci venčanja.',
        ],
        'invitation' => [
            'personalized_message_placeholder' => 'Ova poruka će biti prikazana na vrhu njihove pozivnice...',
        ],
        'plus_one' => [
            'label' => '+1 opcija',
            'description' => 'Omogućite gostu da doda još jednu osobu.',
            'disabled_helper' => 'Ova opcija je dostupna samo za grupe sa jednim gostom.',
            'allowed' => 'Dozvoljen +1',
        ],
        'timeline' => [
            'description' => 'Upravljajte vidljivošću stavki vremenske linije za ovu grupu.',
            'all_items_visible' => 'Sve stavke vremenske linije su vidljive',
            'hidden_items' => 'Skrivene stavke vremenske linije',
        ],
    ],

    'guests' => [
        'form' => [
            'notes_placeholder' => 'npr. Alergije, posebne želje...',
        ],
        'companions' => [
            'select_or_create' => 'Izaberite postojećeg gosta ili kreirajte novog.',
            'guest_required' => 'Morate izabrati gosta ili kreirati novog.',
            'remove_confirmation' => 'Da li ste sigurni da želite da uklonite ovog pratioca sa spiska?',
            'description' => 'Osobe koje dolaze sa ovim gostom.',
            'empty' => 'Nema dodatnih pratilaca',
            'add_description' => 'Dodajte posetioce koji dolaze sa ovim gostom.',
        ],
        'empty' => 'Nema informacija o gostu',
        'create_description' => 'Kreirajte gosta i dodajte posetioce koji dolaze sa njim.',
    ],

    'widgets' => [
        'group_views' => [
            'heading' => 'Pregledi po grupama',
        ],
        'guest_age_chart' => [
            'heading' => 'Distribucija po uzrastu',
        ],
        'guest_demographics' => [
            'total_guests' => [
                'description' => 'Ukupan broj svih gostiju u bazi',
            ],
            'age_structure' => [
                'description' => 'Odrasli / Deca / Bebe',
            ],
            'gender_structure' => [
                'description' => 'Muški / Ženski',
            ],
        ],
        'guest_status' => [
            'confirmed' => [
                'description' => 'Gosti koji dolaze',
            ],
            'declined' => [
                'description' => 'Gosti koji ne dolaze',
            ],
            'pending' => [
                'description' => 'Gosti koji se još nisu izjasnili',
            ],
        ],
        'invitation_stats' => [
            'sent_invitations' => [
                'description' => 'Ukupan broj grupa kojima je poslata pozivnica',
            ],
            'total_views' => [
                'description' => 'Koliko puta su sve pozivnice ukupno otvorene',
            ],
        ],
        'guest_gender_chart' => [
            'heading' => 'Distribucija po polu',
        ],
    ],

    'notifications' => [
        'attendance_confirmation_success' => 'Dolazak je uspešno potvrđen.',
    ],
];
