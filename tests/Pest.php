<?php

use App\Enums\FilamentPanel;
use App\Filament\Auth\ManagementLogin;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Browser');

pest()->beforeEach(function (): void {
    // Every Wedding test uses the Wedding panel and an authenticated team member.
    Filament::setCurrentPanel('wedding');
    Filament::bootCurrentPanel();

    $this->user = User::factory()->weddingTeamMember()->create();
    $this->actingAs($this->user, 'wedding');
})->in('Feature/Filament/Wedding');

/**
 * @return array<string, array{
 *     panel: FilamentPanel,
 *     login_page: class-string,
 *     authorized_user: Closure(): User,
 *     unauthorized_users: array<string, Closure(): User>
 * }>
 */
function filamentPanelLoginConfigurations(): array
{
    return [
        'management' => [
            'panel' => FilamentPanel::Management,
            'login_page' => ManagementLogin::class,
            'authorized_user' => fn (): User => User::factory()->managementAdmin()->create(),
            'unauthorized_users' => [
                'wedding team member' => fn (): User => User::factory()->weddingTeamMember()->create(),
            ],
        ],
        'wedding' => [
            'panel' => FilamentPanel::Wedding,
            'login_page' => Login::class,
            'authorized_user' => fn (): User => User::factory()->weddingTeamMember()->create(),
            'unauthorized_users' => [
                'management admin' => fn (): User => User::factory()->managementAdmin()->create(),
                'wedding team member without a team' => fn (): User => User::factory()
                    ->weddingTeamMember()
                    ->create(['team_id' => null]),
            ],
        ],
    ];
}

dataset('filament panels', array_keys(filamentPanelLoginConfigurations()));

function bootFilamentPanel(FilamentPanel $panel): void
{
    Filament::setCurrentPanel($panel->value);
    Filament::bootCurrentPanel();
    auth()->guard($panel->guard())->logout();
}

function createFilamentTestUser(Closure $userFactory): User
{
    return $userFactory();
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
