<?php

declare(strict_types=1);

use App\DTOs\MetaData;
use App\Enums\OpenGraphPlatform;
use Illuminate\Support\Facades\Blade;

test('renders open graph metadata in a chat-style preview', function (): void {
    $metaData = new MetaData(
        title: 'Ana i Marko',
        description: 'Radujemo se što ćete biti deo našeg posebnog dana.',
        image: 'https://example.test/images/wedding.webp',
    );

    $html = Blade::render(
        '<x-open-graph-preview :meta-data="$metaData" url="https://example.test/invitation" />',
        compact('metaData'),
    );

    expect($html)
        ->toContain('Ana i Marko')
        ->toContain('Radujemo se što ćete biti deo našeg posebnog dana.')
        ->toContain('https://example.test/images/wedding.webp')
        ->toContain('example.test')
        ->toContain(__('wedding.manage_wedding.meta.preview.tabs_label'))
        ->toContain(__('wedding.manage_wedding.meta.preview.callout.heading'))
        ->toContain(__('wedding.manage_wedding.meta.preview.platforms.whatsapp.label'))
        ->toContain(__('wedding.manage_wedding.meta.preview.platforms.viber.label'))
        ->toContain(__('wedding.manage_wedding.meta.preview.platforms.messenger.label'))
        ->toContain(__('wedding.manage_wedding.meta.preview.platforms.telegram.label'))
        ->toContain('data-open-graph-platform="whatsapp"')
        ->toContain('data-open-graph-platform="viber"')
        ->toContain('data-open-graph-platform="messenger"')
        ->toContain('data-open-graph-platform="telegram"')
        ->toContain('fi-callout')
        ->toContain('max-w-md')
        ->toContain('aspect-[1.91/1]')
        ->toContain('aspect-video')
        ->toContain('aspect-square')
        ->not->toContain('unsaved');
});

test('renders only configured platforms and can hide the preview note', function (): void {
    $metaData = new MetaData(
        title: 'Ana i Marko',
        description: 'Radujemo se posebnom danu.',
        image: 'https://example.test/images/wedding.webp',
    );

    $platforms = [OpenGraphPlatform::WhatsApp, OpenGraphPlatform::Telegram];

    $html = Blade::render(
        '<x-open-graph-preview :meta-data="$metaData" :platforms="$platforms" :show-note="false" />',
        compact('metaData', 'platforms'),
    );

    expect($html)
        ->toContain(__('wedding.manage_wedding.meta.preview.platforms.whatsapp.label'))
        ->toContain(__('wedding.manage_wedding.meta.preview.platforms.telegram.label'))
        ->toContain('data-open-graph-platform="whatsapp"')
        ->toContain('data-open-graph-platform="telegram"')
        ->not->toContain(__('wedding.manage_wedding.meta.preview.platforms.viber.label'))
        ->not->toContain(__('wedding.manage_wedding.meta.preview.platforms.messenger.label'))
        ->not->toContain(__('wedding.manage_wedding.meta.preview.callout.heading'));
});

test('shows a save reminder when metadata changes are not persisted', function (): void {
    $metaData = new MetaData(
        title: 'Ana i Marko',
        description: 'Radujemo se posebnom danu.',
    );

    $html = Blade::render(
        '<x-open-graph-preview :meta-data="$metaData" :has-unsaved-changes="true" />',
        compact('metaData'),
    );

    expect($html)
        ->toContain(__('wedding.manage_wedding.meta.preview.unsaved'))
        ->toContain(__('wedding.manage_wedding.meta.preview.unsaved_tooltip'))
        ->toContain('fi-callout')
        ->toContain('role="status"');
});
