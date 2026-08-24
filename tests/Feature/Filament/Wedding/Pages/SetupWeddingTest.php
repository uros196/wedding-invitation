<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Filament\Wedding\Pages\Dashboard;
use App\Filament\Wedding\Pages\ManageWedding\ManageWedding;
use App\Filament\Wedding\Pages\SetupWedding\SetupWedding;
use App\Filament\Wedding\Resources\Groups\GroupResource;
use App\Filament\Wedding\Resources\Guests\GuestResource;
use App\Filament\Wedding\Widgets\InvitationCreatorWidget;
use App\Filament\Wedding\Widgets\InvitationStats;
use App\Filament\Wedding\Widgets\WeddingSetupWidget;
use App\Models\Wedding;
use App\Services\WeddingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('offers setup while a wedding is missing', function (): void {
    $this->user->team->wedding()->delete();
    $this->user->unsetRelation('team');

    expect(SetupWedding::canAccess())->toBeTrue()
        ->and(InvitationCreatorWidget::canView())->toBeFalse()
        ->and(WeddingSetupWidget::canView())->toBeTrue()
        ->and((new Dashboard)->getWidgets())->toBe([WeddingSetupWidget::class]);

    $this->get(SetupWedding::getUrl())->assertSuccessful();
    $this->get(ManageWedding::getUrl())->assertForbidden();
    $this->get(GroupResource::getUrl())->assertForbidden();
    $this->get(GuestResource::getUrl())->assertForbidden();
});

test('offers setup while a wedding is in draft', function (): void {
    $this->user->team->wedding->update(['status' => Status::Draft]);

    expect(SetupWedding::canAccess())->toBeTrue()
        ->and(InvitationCreatorWidget::canView())->toBeFalse()
        ->and(WeddingSetupWidget::canView())->toBeTrue()
        ->and((new Dashboard)->getWidgets())->toBe([WeddingSetupWidget::class]);

    $this->get(SetupWedding::getUrl())->assertSuccessful();
    $this->get(ManageWedding::getUrl())->assertForbidden();
    $this->get(GroupResource::getUrl())->assertForbidden();
    $this->get(GuestResource::getUrl())->assertForbidden();
});

test('does not expose setup after the wedding is published', function (): void {
    $this->user->team->wedding->update(['status' => Status::Published]);

    expect(SetupWedding::canAccess())->toBeFalse()
        ->and(InvitationCreatorWidget::canView())->toBeTrue()
        ->and(WeddingSetupWidget::canView())->toBeFalse()
        ->and((new Dashboard)->getWidgets())->toContain(InvitationStats::class)
        ->and((new Dashboard)->getWidgets())->not->toContain(WeddingSetupWidget::class);

    $this->get(SetupWedding::getUrl())->assertForbidden();
    $this->get(ManageWedding::getUrl())->assertSuccessful();
});

test('publishes a wedding after the complete setup form is valid', function (): void {
    Storage::fake('public');
    $this->user->team->wedding->update(['status' => Status::Draft]);

    $component = Livewire::test(SetupWedding::class)
        ->fillForm([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'wedding_date' => '2027-07-10',
            'rsvp_deadline' => '2027-06-30 18:00:00',
            'welcome_text' => '<p>Welcome to our wedding.</p>',
            'Hero' => [UploadedFile::fake()->image('hero.jpg', 800, 1000)],
        ])
        ->call('publish')
        ->assertRedirect(Dashboard::getUrl());

    expect(Wedding::query()->where('team_id', $this->user->team_id)->value('status'))
        ->toBe(Status::Published);

    $component->assertNotified();

});

test('does not publish a wedding when required setup information is missing', function (): void {
    $this->user->team->wedding()->delete();

    Livewire::test(SetupWedding::class)
        ->fillForm([])
        ->call('publish')
        ->assertHasFormErrors([
            'bride_name' => 'required',
            'groom_name' => 'required',
            'wedding_date' => 'required',
            'rsvp_deadline' => 'required',
            'welcome_text' => 'required',
            'Hero' => 'required',
        ])
        ->assertNotNotified();

    expect(Wedding::withoutPublish()->value('status'))->toBe(Status::Draft);
});

test('does not publish an incomplete draft through the wedding service', function (): void {
    $wedding = $this->user->team->wedding;
    $wedding->update(['status' => Status::Draft]);

    expect(fn (): Wedding => resolve(WeddingService::class)->publishWedding($wedding, $this->user))
        ->toThrow(HttpException::class);

    expect($wedding->refresh()->status)->toBe(Status::Draft);
});
