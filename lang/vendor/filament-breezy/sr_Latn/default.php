<?php

return [
    'password_confirm' => [
        'heading' => 'Potvrda lozinke',
        'description' => 'Potvrdite lozinku da biste završili ovu radnju.',
        'current_password' => 'Trenutna lozinka',
    ],
    'two_factor' => [
        'heading' => 'Dvofaktorska provera',
        'description' => 'Potvrdite pristup svom nalogu unosom koda koji je prikazan u vašoj aplikaciji za autentifikaciju.',
        'code_placeholder' => 'XXX-XXX',
        'recovery' => [
            'heading' => 'Dvofaktorska provera',
            'description' => 'Potvrdite pristup svom nalogu unosom jednog od rezervnih kodova za oporavak.',
        ],
        'recovery_code_placeholder' => 'abcdef-98765',
        'recovery_code_text' => 'Izgubili ste uređaj?',
        'recovery_code_link' => 'Koristi kod za oporavak',
        'back_to_login_link' => 'Nazad na prijavu',
    ],
    'passkeys' => [
        'authenticate_using_passkey' => [
            'label' => 'Prijavi se pomoću pristupnog ključa',
        ],
        'invalid' => 'Prijava pomoću datog pristupnog ključa nije uspela.',
    ],
    'profile' => [
        'account' => 'Nalog',
        'profile' => 'Profil',
        'my_profile' => 'Moj profil',
        'subheading' => 'Upravljajte svojim korisničkim profilom.',
        'personal_info' => [
            'heading' => 'Lični podaci',
            'subheading' => 'Upravljajte svojim ličnim podacima.',
            'submit' => [
                'label' => 'Ažuriraj',
            ],
            'notify' => 'Profil je uspešno ažuriran!',
        ],
        'password' => [
            'heading' => 'Lozinka',
            'subheading' => 'Mora sadržati najmanje 8 karaktera.',
            'submit' => [
                'label' => 'Ažuriraj',
            ],
            'notify' => 'Lozinka je uspešno ažurirana!',
        ],
        '2fa' => [
            'title' => 'Dvofaktorska autentifikacija',
            'description' => 'Upravljajte dvofaktorskom autentifikacijom za svoj nalog (preporučeno).',
            'actions' => [
                'enable' => 'Omogući',
                'regenerate_codes' => 'Ponovo generiši kodove za oporavak',
                'disable' => 'Onemogući',
                'confirm_finish' => 'Potvrdi i završi',
                'cancel_setup' => 'Otkaži podešavanje',
                'confirm' => 'Potvrdi',
            ],
            'setup_key' => 'Ključ za podešavanje',
            'must_enable' => 'Morate omogućiti dvofaktorsku autentifikaciju da biste koristili ovu aplikaciju.',
            'not_enabled' => [
                'title' => 'Niste omogućili dvofaktorsku autentifikaciju.',
                'description' => 'Kada je dvofaktorska autentifikacija omogućena, tokom prijave biće zatražen bezbedan, nasumično generisan token. Ovaj token možete preuzeti pomoću aplikacije za autentifikaciju na telefonu (npr. Microsoft Authenticator ili Google Authenticator).',
            ],
            'finish_enabling' => [
                'title' => 'Završite omogućavanje dvofaktorske autentifikacije.',
                'description' => 'Da biste završili omogućavanje dvofaktorske autentifikacije, skenirajte sledeći QR kod pomoću aplikacije za autentifikaciju na telefonu ili unesite ključ za podešavanje i obezbedite generisani kod za jednokratnu upotrebu (OTP).',
            ],
            'enabled' => [
                'notify' => 'Dvofaktorska autentifikacija je omogućena.',
                'title' => 'Omogućili ste dvofaktorsku autentifikaciju!',
                'description' => 'Dvofaktorska autentifikacija je sada omogućena. Ovo dodatno štiti vaš nalog.',
                'store_codes' => 'Ovi kodovi mogu da se koriste za vraćanje pristupa nalogu ako izgubite uređaj. Upozorenje! Kodovi će biti prikazani samo jednom.',
            ],
            'disabling' => [
                'notify' => 'Dvofaktorska autentifikacija je onemogućena.',
            ],
            'regenerate_codes' => [
                'notify' => 'Novi kodovi za oporavak su generisani.',
            ],
            'confirmation' => [
                'success_notification' => 'Kod je potvrđen. Dvofaktorska autentifikacija je omogućena.',
                'invalid_code' => 'Uneli ste nevažeći kod.',
            ],
        ],
        'sanctum' => [
            'title' => 'API tokeni',
            'description' => 'Upravljajte API tokenima koji omogućavaju uslugama trećih strana da pristupe ovoj aplikaciji u vaše ime.',
            'create' => [
                'notify' => 'Token je uspešno kreiran!',
                'message' => 'Vaš token će biti prikazan samo jednom prilikom kreiranja. Ako ga izgubite, moraćete da ga obrišete i kreirate novi.',
                'submit' => [
                    'label' => 'Kreiraj',
                ],
            ],
            'update' => [
                'notify' => 'Token je uspešno ažuriran!',
                'submit' => [
                    'label' => 'Ažuriraj',
                ],
            ],
            'copied' => [
                'label' => 'Kopirao/la sam svoj token',
            ],
        ],
        'browser_sessions' => [
            'heading' => 'Sesije pregledača',
            'subheading' => 'Upravljajte svojim aktivnim sesijama.',
            'label' => 'Sesije pregledača',
            'content' => 'Ako je potrebno, možete se odjaviti sa svih drugih sesija pregledača na svim svojim uređajima. Neke od vaših nedavnih sesija navedene su u nastavku, ali ovaj spisak možda nije potpun. Ako smatrate da je vaš nalog ugrožen, trebalo bi da ažurirate i lozinku.',
            'device' => 'Ovaj uređaj',
            'last_active' => 'Poslednja aktivnost',
            'logout_other_sessions' => 'Odjavi ostale sesije pregledača',
            'logout_heading' => 'Odjava ostalih sesija pregledača',
            'logout_description' => 'Unesite lozinku da biste potvrdili odjavljivanje sa svih drugih sesija pregledača na svim svojim uređajima.',
            'logout_action' => 'Odjavi ostale sesije pregledača',
            'incorrect_password' => 'Uneta lozinka nije ispravna. Pokušajte ponovo.',
            'logout_success' => 'Sve ostale sesije pregledača su uspešno odjavljene.',
        ],
        'passkeys' => [
            'heading' => 'Pristupni ključevi',
            'description' => 'Upravljajte pristupnim ključevima za bezbedan pristup nalogu bez lozinke na različitim uređajima.',
            'create' => [
                'submit' => [
                    'label' => 'Kreiraj',
                    'submit_label' => 'Kreiraj i autentifikuj',
                ],
                'error_message' => 'Došlo je do greške prilikom generisanja pristupnog ključa.',
                'success_message' => 'Pristupni ključ je uspešno kreiran.',
            ],
            'update' => [
                'submit' => [
                    'label' => 'Ažuriraj',
                ],
            ],
        ],
    ],
    'clipboard' => [
        'link' => 'Kopiraj u privremenu memoriju',
        'tooltip' => 'Kopirano!',
    ],
    'fields' => [
        'avatar' => 'Avatar',
        'email' => 'Imejl',
        'login' => 'Prijava',
        'name' => 'Ime',
        'password' => 'Lozinka',
        'password_confirm' => 'Potvrda lozinke',
        'new_password' => 'Nova lozinka',
        'new_password_confirmation' => 'Potvrda lozinke',
        'token_name' => 'Naziv tokena',
        'token_expiry' => 'Isticanje tokena',
        'abilities' => 'Dozvole',
        '2fa_code' => 'Kod',
        '2fa_recovery_code' => 'Kod za oporavak',
        'created' => 'Kreirano',
        'expires' => 'Ističe',
        'passkey_name' => 'Naziv pristupnog ključa',
        'last_used_at' => 'Poslednji put korišćeno',
    ],
    'permissions' => [
        'create' => 'Kreiraj',
        'view' => 'Pregledaj',
        'update' => 'Ažuriraj',
        'delete' => 'Obriši',
    ],
    'or' => 'Ili',
    'cancel' => 'Otkaži',
    'login' => [
        'username_or_email' => 'Korisničko ime ili imejl',
        'forgot_password_link' => 'Zaboravili ste lozinku?',
        'create_an_account' => 'Kreiraj nalog',
    ],
    'registration' => [
        'title' => 'Registracija',
        'heading' => 'Kreiraj novi nalog',
        'submit' => [
            'label' => 'Registruj se',
        ],
        'notification_unique' => 'Nalog sa ovom imejl adresom već postoji. Prijavite se.',
    ],
    'reset_password' => [
        'title' => 'Zaboravili ste lozinku?',
        'heading' => 'Resetovanje lozinke',
        'submit' => [
            'label' => 'Pošalji',
        ],
        'notification_error' => 'Greška: Pokušajte ponovo kasnije.',
        'notification_error_link_text' => 'Pokušaj ponovo',
        'notification_success' => 'Proverite imejl za dodatna uputstva!',
    ],
    'verification' => [
        'title' => 'Potvrdite imejl',
        'heading' => 'Potrebna je potvrda imejla',
        'submit' => [
            'label' => 'Odjavi se',
        ],
        'notification_success' => 'Proverite imejl za dodatna uputstva!',
        'notification_resend' => 'Poslat je novi imejl za potvrdu.',
        'before_proceeding' => 'Pre nego što nastavite, proverite imejl i pronađite link za potvrdu.',
        'not_receive' => 'Ako niste primili imejl,',
        'request_another' => 'kliknite ovde da zatražite novi',
    ],
];
