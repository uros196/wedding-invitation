<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Livewire\Livewire;

test('shows the login page to guests for every panel', function (string $panelName): void {
    $configuration = filamentPanelLoginConfigurations()[$panelName];

    bootFilamentPanel($configuration['panel']);

    $this->get(Filament::getLoginUrl())
        ->assertSuccessful();
})->with('filament panels');

test('redirects guests to the correct panel login page', function (string $panelName): void {
    $configuration = filamentPanelLoginConfigurations()[$panelName];

    bootFilamentPanel($configuration['panel']);

    $panelUrl = Filament::getUrl();
    $loginUrl = Filament::getLoginUrl();

    $this->get($panelUrl)
        ->assertRedirect($loginUrl);
})->with('filament panels');

test('allows an authorized user to log in to the matching panel', function (string $panelName): void {
    $configuration = filamentPanelLoginConfigurations()[$panelName];
    $panel = $configuration['panel'];

    bootFilamentPanel($panel);

    $user = createFilamentTestUser($configuration['authorized_user']);
    $panelUrl = Filament::getUrl();

    Livewire::test($configuration['login_page'])
        ->fillForm([
            'email' => $user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertRedirect($panelUrl);

    expect(auth()->guard($panel->guard())->id())->toBe($user->getKey());
})->with('filament panels');

test('rejects invalid login credentials on every panel', function (string $panelName): void {
    $configuration = filamentPanelLoginConfigurations()[$panelName];
    $panel = $configuration['panel'];

    bootFilamentPanel($panel);

    $user = createFilamentTestUser($configuration['authorized_user']);

    foreach ([
        ['email' => $user->email, 'password' => 'wrong-password'],
        ['email' => 'unknown@example.com', 'password' => 'password'],
    ] as $credentials) {
        Livewire::test($configuration['login_page'])
            ->fillForm($credentials)
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        expect(auth()->guard($panel->guard())->check())->toBeFalse();
    }
})->with('filament panels');

test('validates required login fields on every panel', function (string $panelName): void {
    $configuration = filamentPanelLoginConfigurations()[$panelName];
    $panel = $configuration['panel'];

    bootFilamentPanel($panel);

    Livewire::test($configuration['login_page'])
        ->fillForm([
            'email' => null,
            'password' => null,
        ])
        ->call('authenticate')
        ->assertHasFormErrors([
            'email' => 'required',
            'password' => 'required',
        ]);
})->with('filament panels');

test('validates the email format on every panel', function (string $panelName): void {
    $configuration = filamentPanelLoginConfigurations()[$panelName];
    $panel = $configuration['panel'];

    bootFilamentPanel($panel);

    Livewire::test($configuration['login_page'])
        ->fillForm([
            'email' => 'not-an-email',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email' => 'email']);
})->with('filament panels');

test('rejects users who are not authorized for the panel during login', function (string $panelName): void {
    $configuration = filamentPanelLoginConfigurations()[$panelName];
    $panel = $configuration['panel'];

    foreach ($configuration['unauthorized_users'] as $userFactory) {
        bootFilamentPanel($panel);

        $user = createFilamentTestUser($userFactory);

        Livewire::test($configuration['login_page'])
            ->fillForm([
                'email' => $user->email,
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        expect(auth()->guard($panel->guard())->check())->toBeFalse();
    }
})->with('filament panels');

test('allows only authorized users to access the panel', function (string $panelName): void {
    $configuration = filamentPanelLoginConfigurations()[$panelName];
    $panel = $configuration['panel'];

    bootFilamentPanel($panel);

    $user = createFilamentTestUser($configuration['authorized_user']);
    $this->actingAs($user, $panel->guard());

    $this->get(Filament::getUrl())
        ->assertSuccessful();

    foreach ($configuration['unauthorized_users'] as $userFactory) {
        bootFilamentPanel($panel);

        $unauthorizedUser = createFilamentTestUser($userFactory);
        $this->actingAs($unauthorizedUser, $panel->guard());

        $this->get(Filament::getUrl())
            ->assertForbidden();
    }
})->with('filament panels');

test('keeps authentication isolated between panels', function (string $panelName): void {
    $configurations = filamentPanelLoginConfigurations();
    $configuration = $configurations[$panelName];
    $panel = $configuration['panel'];

    bootFilamentPanel($panel);

    $user = createFilamentTestUser($configuration['authorized_user']);
    $this->actingAs($user, $panel->guard());

    $otherPanelName = array_values(array_diff(array_keys($configurations), [$panelName]))[0];
    $otherPanel = $configurations[$otherPanelName]['panel'];

    bootFilamentPanel($otherPanel);

    $otherPanelUrl = Filament::getUrl();
    $otherLoginUrl = Filament::getLoginUrl();

    $this->get($otherPanelUrl)
        ->assertRedirect($otherLoginUrl);
})->with('filament panels');

test('redirects authenticated users away from the panel login page', function (string $panelName): void {
    $configuration = filamentPanelLoginConfigurations()[$panelName];
    $panel = $configuration['panel'];

    bootFilamentPanel($panel);

    $user = createFilamentTestUser($configuration['authorized_user']);
    $this->actingAs($user, $panel->guard());

    $panelUrl = Filament::getUrl();

    $this->get(Filament::getLoginUrl())
        ->assertRedirect($panelUrl);
})->with('filament panels');
