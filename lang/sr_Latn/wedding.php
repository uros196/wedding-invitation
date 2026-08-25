<?php

return [
    'title' => 'Pozivnica za venčanje',
    'greeting' => 'Pozvani ste na naše venčanje. Pridružite nam se u proslavi ljubavi!',

    'setup' => [
        'title' => 'Podešavanje venčanja',
        'intro' => [
            'heading' => 'Hajde da podesimo vašu pozivnicu',
            'description' => 'Prvo ćemo uneti najvažnije podatke. Sve možete izmeniti i kasnije.',
            'duration' => 'Podešavanje obično traje oko 3 minuta.',
        ],
        'steps' => [
            'basics' => [
                'title' => 'Osnovni podaci',
                'description' => 'Imena i datum venčanja',
                'heading' => 'Počnimo od osnova',
                'help' => 'Ovi podaci su potrebni da napravimo osnovu vaše pozivnice.',
            ],
            'appearance' => [
                'title' => 'Izgled pozivnice',
                'description' => 'Slika i poruka dobrodošlice',
                'heading' => 'Dodajte lični pečat pozivnici',
                'help' => 'Izaberite sliku i poruku koju će gosti prvo videti.',
                'image_callout' => [
                    'heading' => 'Važan korak za glavnu sliku',
                    'description' => 'Slika mora da bude u jednom od ponuđenih odnosa stranica (aspect ratio): 9:16 ili 4:5. Nakon otpremanja otvorite uređivanje slike, izaberite format koji vam najviše odgovara i kropujte je tako da važan deo ostane u kadru.',
                ],
            ],
            'guest_info' => [
                'title' => 'Odgovori gostiju',
                'description' => 'Rok za potvrdu dolaska',
                'heading' => 'Podesite rok za odgovor',
                'help' => 'Izaberite do kada gosti treba da potvrde ili odbiju dolazak.',
            ],
        ],
        'publish_action' => 'Završi i objavi pozivnicu',
        'dashboard_action' => 'Nazad na početnu stranu',
    ],

    'manage_wedding' => [
        'help_action' => 'Dodatne informacije',
        'basic_information' => [
            'callout' => [
                'description' => 'Unesite imena i datume koji će se prikazivati na pozivnici. Unesite imena tačno onako kako želite da ih gosti vide i podesite rok za prijavu pre datuma venčanja.',
            ],
            'bride_name_placeholder' => 'npr. Ana',
            'groom_name_placeholder' => 'npr. Marko',
            'wedding_date_placeholder' => 'Izaberite datum i vreme venčanja',
            'rsvp_deadline_placeholder' => 'Izaberite do kada gosti odgovaraju',
            'rsvp_deadline_help' => 'Gosti mogu da potvrde ili odbiju dolazak do ovog datuma i vremena.',
        ],
        'main_image_description' => 'Ova slika se prikazuje na vrhu pozivnice.',
        'main_image_help' => 'Izaberite glavnu fotografiju koju će gosti prvo videti. Nakon otpremanja možete da je isečete.',
        'invitation_text' => [
            'description' => 'Napišite poruku dobrodošlice koju će gosti pročitati na pozivnici.',
            'help' => 'Ovo je glavna poruka na pozivnici. Po potrebi koristite uređivač za podebljan tekst ili liste.',
            'welcome_text_placeholder' => 'npr. Drago nam je da vas pozovemo da zajedno proslavimo naš poseban dan...',
        ],
        'schedule' => [
            'description' => 'Dodajte događaje redosledom kojim će se održavati.',
            'help' => 'Dodajte po jednu stavku za svaki deo proslave. Gosti će videti samo stavke koje su označene kao vidljive.',
            'event_name_placeholder' => 'npr. Ceremonija',
            'time_placeholder' => 'npr. 16:00',
            'address_placeholder' => 'npr. Hram Svetog Save',
            'map_link_placeholder' => 'Nalepite link ka Google mapi',
            'icon_help' => 'Izaberite ikonicu koja će gostima pomoći da prepoznaju ovaj događaj.',
            'visibility_help' => 'Isključite ovo ako želite da sakrijete događaj od gostiju, a da ga ne obrišete.',
            'visibility_toggle' => 'Vidljivo gostima',
            'visibility_inactive_label' => 'Neaktivno',
            'visibility_all' => 'Vidljivo svim grupama',
            'visibility_custom' => 'Prilagođena vidljivost grupa',
            'visibility_manage' => 'Izaberite grupe koje mogu da vide ovaj događaj.',
            'visibility_save_first' => 'Sačuvajte stavku vremenske linije pre izbora grupa.',
            'visibility_modal_heading' => 'Izbor grupa za :event',
            'visibility_modal_description' => 'Nove stavke vremenske linije su podrazumevano vidljive svim grupama. Isključite grupe koje ne treba da vide ovaj događaj.',
            'visibility_save' => 'Sačuvaj vidljivost',
            'visible_groups' => 'Grupe koje mogu da vide ovaj događaj',
            'visibility_select_all' => 'Označi sve',
            'visibility_deselect_all' => 'Poništi sve',
        ],
        'memory_wall' => [
            'callout' => [
                'description' => 'Omogućite gostima da podele fotografije i poruke sa proslave. Kada je uključen, mogu da koriste link ili QR kod, a vi možete da podesite do kada je slanje otvoreno.',
            ],
            'enable_help' => 'Uključite ovo da biste napravili stranicu na kojoj gosti mogu da podele fotografije i uspomene.',
            'open_until_placeholder' => 'Izaberite do kada je moguće slanje fotografija i poruka',
            'open_until_help' => 'Datum mora biti posle venčanja i u okviru dozvoljenog perioda.',
            'url_help' => 'Ovaj link se kreira automatski. Kopirajte ga i podelite sa gostima.',
            'qr_code_help' => 'Odštampajte ili prikažite ovaj QR kod kako bi gosti brzo otvorili zid uspomena.',
        ],
        'meta' => [
            'description' => 'Koristi se za generisanje pregleda linka kada se pozivnica podeli.',
            'image_fallback' => 'Opciono. Ako se ostavi prazno, koristiće se glavna slika iznad.',
            'help' => 'Ova podešavanja određuju naslov, tekst i sliku koji se prikazuju kada se link pozivnice podeli u poruci ili na društvenoj mreži.',
            'title_help' => 'Ovo je naslov koji se prikazuje u pregledu linka. Ostavite prazno da se koristi podrazumevani naslov venčanja.',
            'description_help' => 'Ovo je tekst ispod naslova u pregledu linka. Ostavite prazno da se koristi podrazumevani pozdrav.',
            'image_help' => 'Opciono. Ostavite prazno da se koristi glavna slika pozivnice.',
            'preview' => [
                'heading' => 'Pregled linka',
                'description' => 'Primer kako pozivnica može izgledati kada se podeli u čet aplikaciji.',
                'tabs_label' => 'Aplikacije za pregled linka',
                'empty' => 'Nijedna aplikacija za pregled linka nije izabrana.',
                'callout' => [
                    'heading' => 'Napomena o pregledu',
                    'description' => 'Ovo je samo pregled. Svaka čet aplikacija ili društvena mreža može drugačije da prikaže link, uključujući isecanje i odnos stranica slike, pa se podeljena pozivnica može razlikovati od ovog primera.',
                ],
                'platforms' => [
                    'whatsapp' => [
                        'label' => 'WhatsApp',
                    ],
                    'viber' => [
                        'label' => 'Viber',
                    ],
                    'messenger' => [
                        'label' => 'Messenger',
                    ],
                    'telegram' => [
                        'label' => 'Telegram',
                    ],
                ],
                'unsaved' => 'Sačuvajte izmene da biste ažurirali ovaj pregled i prikaz deljenog linka.',
                'unsaved_tooltip' => 'Postoje nesnimljene izmene',
                'image_alt' => 'Slika iz pregleda pozivnice',
                'image_placeholder' => 'Slika pozivnice',
            ],
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
        'quick_create' => [
            'title' => 'Kreiranje grupnih pozivnica',
            'intro' => [
                'heading' => 'Napravite pozivnicu bez komplikovanja',
                'description' => 'Prvo napravite grupu, a zatim dodajte goste jednog po jednog. Sve ostalo možete urediti kasnije.',
            ],
            'steps' => [
                'label' => 'Koraci za kreiranje pozivnice',
                'group' => '1. Napravite grupu',
                'guests' => '2. Dodajte goste',
            ],
            'group' => [
                'heading' => 'Prvo napravite grupu',
                'description' => 'Jedna grupa dobija jedan link pozivnice. Unesite naziv po kom ćete lako prepoznati kome je pozivnica namenjena, na primer porodica ili grupa prijatelja.',
                'create_action' => 'Napravi grupu i nastavi',
            ],
            'guest' => [
                'heading' => 'Sada dodajte goste',
                'description' => 'Unesite jednog gosta po jednom. Posle čuvanja polja će biti spremna za sledećeg gosta.',
                'form_description' => 'Dovoljno je da unesete ime, prezime i uzrast. Ostale podatke možete dopuniti kasnije.',
                'current_group' => 'Trenutna grupa: :name',
                'added_count' => 'Dodato gostiju: :count',
                'empty' => 'Još niste dodali nijednog gosta.',
                'add_action' => 'Dodaj gosta',
                'finish_action' => 'Završi ovu grupu',
                'another_action' => 'Napravi novu pozivnicu',
            ],
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
        'wedding_status' => [
            'heading' => 'Javni pristup',
            'rsvp' => [
                'open' => 'Gosti mogu da potvrde ili odbiju dolazak.',
                'closed' => 'Odgovori gostiju više nisu dostupni.',
                'not_set' => 'Podesite rok za prijavu u detaljima venčanja.',
            ],
            'memory_wall' => [
                'open' => 'Gosti mogu da dele fotografije i poruke.',
                'closed' => 'Deljenje fotografija i poruka je trenutno zatvoreno.',
                'not_set' => 'Omogućite zid uspomena u detaljima venčanja.',
            ],
        ],
        'group_views' => [
            'heading' => 'Pregledi po grupama',
        ],
        'guest_age_chart' => [
            'heading' => 'Distribucija po uzrastu',
        ],
        'guest_demographics' => [
            'heading' => 'Demografija gostiju',
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
            'heading' => 'Odgovori na pozivnicu',
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
            'heading' => 'Pregled pozivnica',
            'sent_invitations' => [
                'description' => 'Ukupan broj grupa kojima je poslata pozivnica',
            ],
            'total_views' => [
                'description' => 'Koliko puta su sve pozivnice ukupno otvorene',
            ],
        ],
        'invitation_creator' => [
            'heading' => 'Kreiranje pozivnica',
            'description' => 'Kliknite ovde da napravite grupu i dodate goste jednog po jednog.',
        ],
        'wedding_setup' => [
            'heading' => 'Podešavanje venčanja',
            'stat' => 'Pozivnica za venčanje',
            'status' => 'Još nije objavljena',
            'description' => 'Nastavite podešavanje da otključate goste, grupe i ostatak početne strane.',
        ],
        'guest_gender_chart' => [
            'heading' => 'Distribucija po polu',
        ],
    ],

    'notifications' => [
        'attendance_confirmation_success' => 'Dolazak je uspešno potvrđen.',
        'group_created' => 'Grupa je napravljena. Sada dodajte goste jednog po jednog.',
        'guest_added' => 'Gost je dodat.',
        'wedding_published' => 'Vaša pozivnica za venčanje je objavljena.',
    ],
];
