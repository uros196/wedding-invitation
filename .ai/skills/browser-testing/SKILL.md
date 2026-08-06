---
name: browser-testing
description: "Use this skill for Pest 4 browser tests in this Laravel application. Trigger when testing real browser interactions, JavaScript behavior, complete user journeys, smoke testing, responsive layouts, color schemes, accessibility, screenshots, or visual regressions. Keep browser tests in tests/Browser and use feature-testing for HTTP contracts, authorization, persistence, and API workflows."
license: MIT
metadata:
    author: laravel
---

# Pest 4 Browser Testing

Use Pest 4 browser testing for behavior that must be verified in a real browser. Pest's browser plugin is Playwright-based and can use Laravel's normal test case, factories, authentication helpers, events, notifications, and database refreshes.

Browser tests complement, rather than replace, the existing feature tests:

- `tests/Feature` verifies HTTP/Inertia/Filament contracts, authorization, validation, persistence, and side effects.
- `tests/Browser` verifies what a real user can see and do, including JavaScript, navigation, forms, responsive layouts, accessibility, and complete workflows.
- Reuse the same factories, ownership rules, security cases, naming conventions, and Arrange–Act–Assert structure from `feature-testing`.
- Do not move backend coverage into a slow browser test just because the behavior is reachable through a page.

## Project Rules

- Keep all real-browser tests under `tests/Browser`.
- Group browser tests by interface and domain, matching the existing feature-test casing:

```text
tests/Browser/
├── Pages/
│   └── wedding/
│       └── MemoryWallShowTest.php
├── Workflows/
│   └── wedding/
│       └── RsvpTest.php
└── SmokeTest.php
```

- Use one focused file per public page or meaningful user workflow. Do not create one catch-all browser test file.
- Use `test()` because the existing project browser-adjacent and feature tests use `test()`, not `it()`.
- Add `declare(strict_types=1);` and explicit `: void` return types to test closures, matching the existing test suite.
- Use model factories and named factory states. Associate records explicitly with their wedding or other owner using `->for(...)`.
- Create fixtures before faking events, notifications, mail, queues, or external HTTP calls.
- Keep important persistence and security assertions in feature tests as well; a browser assertion that an element is hidden is not an authorization test.
- Never delete, disable, weaken, or skip a failing test to make browser runs pass.

## Installation and Prerequisites

This project uses the Pest browser plugin and Playwright. When setting up a fresh clone, install the Pest browser plugin and Playwright:

```shell
composer require pestphp/pest-plugin-browser --dev
npm install playwright@latest
npx playwright install
```

The plugin and browser binaries are separate requirements. Local and CI environments must both have the Playwright browser that the suite uses. On Linux CI, install system dependencies when required, for example:

```shell
npx playwright install --with-deps chromium
```

Before diagnosing a browser failure, confirm that the project dependencies, Playwright browser, application key, database, and frontend assets are available. If an Inertia or Vite page is blank, build or run the frontend according to the project's normal workflow before changing the test.

## Pest Configuration

The current `tests/Pest.php` applies `Tests\TestCase` and `RefreshDatabase` to `Feature` only. When browser testing is enabled, extend the same scope to `Browser`:

```php
pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Browser');
```

Keep the existing scoped `beforeEach` setup unchanged. In particular, the wedding Filament authentication setup in `tests/Pest.php` is scoped to `Feature/Filament/Wedding`; it must not silently authenticate every public browser test.

If a browser test requires a logged-in user, either test the login UI itself or explicitly arrange the authenticated user in the test:

```php
$this->actingAs($user, 'wedding');
$page = $this->visit('/wedding/dashboard');
```

Use the correct guard for the route under test. Prefer the application's named routes when constructing URLs, for example `$this->visit(route('group.show', ['group' => $group->uuid]))`.

## Creating and Running Tests

Laravel's test generator defaults to `tests/Feature`. Generate a Pest scaffold without adding `Feature/` or `Unit/` to the name, then place the final file under `tests/Browser`:

```shell
php artisan make:test --pest --no-interaction BrowserSmokeTest
```

Move the generated file to its final browser path, such as `tests/Browser/SmokeTest.php`. Do not use `--unit` for a browser test.

Run the smallest relevant scope first:

```shell
php artisan test --compact tests/Browser/Pages/wedding/MemoryWallShowTest.php
php artisan test --compact tests/Browser --filter="memory wall"
```

Run the full browser suite after focused verification:

```shell
php artisan test --compact tests/Browser
```

For larger suites, Pest can run browser tests in parallel or as CI shards:

```shell
vendor/bin/pest --parallel tests/Browser
vendor/bin/pest --shard=1/4 --parallel tests/Browser
```

Do not use parallel execution when tests share external state, fixed accounts, uploaded files, or a non-isolated third-party service. Prefer factories and isolated temporary state instead.

## Basic Browser Test

Use the Pest browser test case's `$this->visit()` method and assert observable page behavior:

```php
<?php

declare(strict_types=1);

test('the home page is usable in a real browser', function (): void {
    $page = $this->visit('/');

    $page->assertSee('Welcome')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
```

Use stable visible text, labels, semantic selectors, and meaningful attributes. Avoid selectors coupled to Tailwind classes or React implementation details. If the product has stable accessibility labels or test-specific attributes, prefer those over DOM depth.

## Complete User Workflows

Test a complete flow from the user's perspective and verify the server-side result when the flow changes state. The following login pattern is adapted from the Smithery skill's example and uses the canonical Pest 4 interaction methods:

```php
<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('a user can sign in through the browser', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $page = $this->visit('/login');

    $page->assertSee('Log in')
        ->type('email', $user->email)
        ->type('password', 'password')
        ->press('Log in')
        ->assertPathIs('/dashboard')
        ->assertSee($user->name)
        ->assertNoJavaScriptErrors();

    $this->assertAuthenticated();
});
```

The Smithery example also uses `fill('field', $value)` and `click('Button')`. Use `fill()` where that alias is available in the installed plugin; use `type()` and `press()` when matching the official Pest 4 examples. Choose selectors based on the actual rendered labels and button text in this application, not copied example text.

For state-changing wedding workflows, arrange the wedding, group, and guests with factories, interact with the visible RSVP form, assert the success or validation message, and keep the authoritative guest/message status assertions in the related feature test. This prevents a browser test from passing while the endpoint stores invalid or cross-wedding data.

## Browser Interactions

Pest browser pages support expressive Playwright-backed interactions. Use only methods supported by the installed Pest version and consult the version-specific documentation when an API is unfamiliar.

Common interactions include:

- `click('Save')` for links and buttons identified by visible text.
- `type('email', 'guest@example.com')` or `fill('email', 'guest@example.com')` for text fields.
- `press('Submit')` or `pressAndWaitFor('Submit', 2)` for keyboard/button actions that require a documented wait.
- `select('#country', 'RS')` for a select field.
- `check('#terms')`, `uncheck('#terms')`, and `radio('#choice')` for form controls.
- `attach('#photo', $path)` for upload workflows when the feature supports uploads.
- `scroll()`, `hover()`, `drag()`, and touch gestures for interactions that are part of the product behavior.
- `waitFor()` or a specific wait assertion when asynchronous UI state must be observed; never add arbitrary `sleep()` calls.

Prefer one user intention per test. Do not test every click sequence in one long method; split authentication, validation, successful submission, and error recovery into focused tests.

## Navigation and Element Assertions

Use behavior-specific assertions instead of checking only that the browser did not crash:

```php
$page->assertTitle('Wedding Invitation')
    ->assertTitleContains('Wedding')
    ->assertSee('RSVP')
    ->assertDontSee('Private administration')
    ->assertVisible('[aria-label="Open RSVP"]')
    ->assertMissing('[role="alert"]')
    ->assertPathIs('/wedding/abc')
    ->assertPathContains('/wedding');
```

For failures, assert the visible validation message, the invalid field state, and that the user remains on the expected page. For authenticated or tenant-scoped pages, also retain feature tests for `assertForbidden()`, `assertNotFound()`, database immutability, and ownership isolation.

## Smoke Testing

Smoke tests quickly exercise several public pages in real browsers and detect JavaScript and console failures. They are useful as a small deployment gate, not as a replacement for focused workflow tests:

```php
<?php

declare(strict_types=1);

test('public pages have no browser errors', function (): void {
    $pages = $this->visit([
        '/',
        '/about',
        '/contact',
    ]);

    $pages->assertNoSmoke()
        ->assertNoAccessibilityIssues();
});
```

`assertNoSmoke()` is shorthand for checking `assertNoJavaScriptErrors()` and `assertNoConsoleLogs()`. Add important page-specific content assertions when a route's rendering contract matters. Keep the route list explicit and update it when a public page is added or removed.

## Responsive Layouts and Color Schemes

Pest uses a desktop viewport by default. Exercise responsive behavior only where the layout has a supported product requirement:

```php
test('the memory wall works on a phone in dark mode', function (): void {
    $page = $this->visit('/memory-wall')
        ->on()->iPhone14Pro()
        ->inDarkMode();

    $page->assertSee('Memory Wall')
        ->assertVisible('[aria-label="Open menu"]')
        ->assertNoJavaScriptErrors();
});
```

Use `->on()->mobile()`, `->on()->tablet()`, `->on()->desktop()`, or a named device such as `->on()->iPhone14Pro()` when appropriate. Use `->inDarkMode()` and `->inLightMode()` for theme coverage. Do not duplicate every test for every viewport; cover the breakpoint-specific behavior and keep the normal flow on the default viewport.

## Accessibility Checks

Use accessibility assertions for pages where accessibility is part of the contract:

```php
test('the RSVP page has no serious accessibility issues', function (): void {
    $this->visit('/rsvp')
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});
```

The default check targets serious issues. If the project intentionally adopts a different severity threshold, document that decision in the test and verify the supported method signature against the installed plugin. Also test keyboard navigation, visible focus, labels, validation announcements, contrast-sensitive themes, and mobile controls when those behaviors are relevant; an automated accessibility scan is not complete accessibility coverage.

## Screenshots and Visual Regression

Use screenshot assertions for stable, high-value visual contracts, not for every browser test:

```php
test('the invitation hero has the approved visual layout', function (): void {
    $this->visit('/invitation')
        ->assertScreenshotMatches();
});
```

For a full-page screenshot or a diagnostic diff, use the documented options for the installed Pest version, for example `assertScreenshotMatches(true, true)`. Keep visual fixtures deterministic: freeze time when necessary, use stable factories, avoid random content, and ensure fonts and frontend assets are loaded consistently.

Add screenshot artifacts to `.gitignore` when visual tests are introduced:

```text
/tests/Browser/Screenshots
```

Commit only intentional baseline images according to the team's visual-regression policy. Do not accept a new baseline merely to hide an unexpected UI change.

## Debugging Browser Failures

Use browser-specific diagnostics before changing assertions:

- Run the focused test with `--debug`.
- Use `$page->debug()` to inspect the current browser state.
- Use `$page->screenshot()` or `$page->screenshotElement()` to capture the failing state.
- Use `$page->tinker()` for an interactive pause when local debugging is appropriate.
- Enable headed mode with the Pest browser configuration when seeing the real interaction is more useful than a screenshot.
- Check recent browser logs separately from old application logs.

Do not replace a missing wait with a long timeout until the actual asynchronous condition is understood. Prefer `pressAndWaitFor()`, `waitFor()`, a URL assertion, or a visible state assertion tied to the real application event.

## Coverage Checklist

Before finalizing a browser test, check the following:

- The test is in the correct `tests/Browser` domain/interface directory and has one clear behavior.
- Factories and ownership relationships are explicit; no production records or shared accounts are required.
- The test covers the smallest valid happy path and relevant visible validation/error states.
- Authentication, authorization, tenant isolation, and persisted state are also covered by feature tests.
- Assertions cover content, navigation, user-visible state, and JavaScript errors where relevant.
- Responsive, dark/light, keyboard, accessibility, upload, duplicate-submit, empty-state, or retry behavior is covered when supported by the feature.
- Selectors are stable and user-oriented, and there are no arbitrary sleeps.
- Browser binaries and frontend assets are available in the environment used for verification.
- The focused browser test, the affected `tests/Browser` subtree, and the related feature tests pass.
