---
name: feature-testing
description: Use when adding, reviewing, or refactoring Laravel Feature tests for HTTP/API endpoints, Inertia responses, Filament panels, Livewire components, or application workflows; defines readable Pest testing practices for these areas.
license: MIT
metadata:
  author: project
---

# Laravel Feature Testing Guide

Use this skill whenever adding, reviewing, or refactoring Laravel Feature tests. Feature tests verify complete application behavior through HTTP, Inertia, JSON, Filament/Livewire, events, and the database. Use Pest 4, Laravel's feature test case, and model factories.

The goal is to test observable behavior and security boundaries, not implementation details. Do not create a separate test style for each feature: use the same Arrange–Act–Assert principles for API and Filament workflows.

## Before Writing Tests

1. Inspect the route, controller, Form Request, policy/middleware, resource/transformer, service, model relationships, events, factories, and existing tests.
2. Identify the endpoint's response contract, authentication guard, tenant/ownership relation, authorization rules, validation, defaults, state transitions, side effects, and destructive actions.
3. For Filament, also inspect the resource's `Pages`, `Schemas`, `Tables`, actions, and relation managers.
4. Check the installed-version documentation for unfamiliar Pest, Laravel, Livewire, or Filament assertions.
5. Reuse the project's existing setup and naming conventions. Do not invent a second authentication, panel, or database bootstrap pattern.

## Test Organization

Group tests by interface and domain:

```text
tests/Feature/Pages/{Context}/{Page}Test.php
tests/Feature/API/{Context}/{Endpoint}Test.php
tests/Feature/Filament/{Panel}/{Resource}/
```

Keep the existing project casing and context names. Use `Pages` for page-rendering requests, including Inertia pages, and `API` for state-changing workflows such as RSVP `POST` endpoints. `API` is an organizational name, not a promise that the response is JSON: inspect the route and assert its actual redirect, HTML, Inertia, or JSON contract. If a feature has both a page and an endpoint, keep them in separate files named after the page or endpoint instead of creating a domain catch-all file.

Use one focused test file per public page or state-changing endpoint when the behavior is substantial. Keep related variants together in that file, such as enabled/disabled state, bot/authenticated access, unknown identifiers, counters, and filtered public data for a page. Split further only when the file covers multiple responsibilities that are difficult to read.

For a complex Filament resource, split by page or responsibility:

```text
{Resource}/
├── List{Resource}Test.php
├── Create{Resource}Test.php
├── View{Resource}Test.php
├── Edit{Resource}Test.php
└── {RelationManager}Test.php
```

Use a shared scoped `beforeEach` when setup is identical for all tests in a subtree. Prefer defining it once in `tests/Pest.php` for a stable directory scope instead of repeating it in every page or resource file. It should only establish stable context, such as the correct panel and authenticated factory user; keep feature-specific records inside each test. Do not apply panel authentication to public page tests.

## Fixture Rules

- Use factories for every model; prefer named states such as `sent()`, `pending()`, or `withMeta()` over duplicated attribute arrays.
- Explicitly associate every resource record with the authenticated tenant/wedding using `->for(...)`.
- Create a second tenant for isolation tests; never rely on a factory's unrelated default owner.
- Create factories before calling `Event::fake()` so model observers can generate UUIDs and defaults.
- Assert persisted models after the Livewire request, not only rendered form state.
- Keep Arrange, Act, and Assert sections obvious; use one behavior per test and datasets for repeated validation cases.
- Add concise comments in English explaining what each important assertion protects.

## General Feature Coverage

Every feature should cover its supported behavior across these boundaries:

- happy path with the smallest valid fixture;
- validation failures and boundary values;
- authentication and authorization failures;
- tenant/ownership isolation and unknown identifiers;
- persisted state, response contract, notifications, redirects, and events;
- repeated, empty, duplicate, stale, or contradictory input where relevant;
- destructive actions and preservation of unrelated records.

Prefer a small number of focused tests with strong assertions over tests that only prove a page returned without an exception.

## HTTP Page and Endpoint Coverage

Test the public contract through named routes. Do not assume that a route called an API returns JSON: first determine whether it renders a page, returns Inertia, redirects after a form submission, or returns JSON.

### Page-rendering requests

- Test public and authenticated access, unknown identifiers, tenant visibility, middleware effects, and the complete rendered-page contract.
- For Inertia responses, verify the component and important props with `assertInertia()`; do not treat a `200` response alone as sufficient.
- Assert counters, filtered relationships, hidden fields, and feature flags that affect what the page can expose.
- Test request-context variants that change observable behavior, such as browser versus crawler user agents or authenticated preview users, with datasets when the assertion is the same.

### State-changing endpoints

- Use the project's normal HTTP method for form/redirect workflows and `getJson()`/`postJson()` for JSON contracts.
- Verify the successful response, redirect/flash or JSON structure, database state transition, and dispatched side effects.
- Verify that invalid, unauthorized, stale, duplicate, or cross-owner requests leave all affected models unchanged.

- Assert the exact successful boundary with `assertSuccessful()`, `assertCreated()`, or the relevant redirect assertion.
- Assert failure boundaries with `assertUnprocessable()`, `assertForbidden()`, `assertNotFound()`, and `assertTooManyRequests()` instead of raw status numbers.
- For JSON responses, verify the structure, important values, resource fields, pagination/meta data, and that private fields are absent.
- For redirect workflows, assert the destination and relevant session flash/errors.
- For Form Requests, cover required, type, format, maximum/minimum, conditional, cross-field, and relationship validation.
- Verify state transitions in the database and assert that invalid requests leave all affected models unchanged.
- Fake events, notifications, mail, queues, and external HTTP calls after fixture creation; assert both dispatch and meaningful payload when they are part of the contract.
- Cover middleware behavior such as throttling, sanitization, CSRF/authentication boundaries, and counters when the endpoint uses it.

## Filament and Livewire Coverage

Cover the following where the resource supports the behavior. Keep these checks in addition to the general and HTTP coverage above.

### List page

- The page mounts for an authorized user.
- Only records belonging to the current tenant are visible.
- A record from another tenant cannot be exposed by changing an ID or search term.
- Search, sorting, filters, empty states, row actions, and bulk actions behave correctly.
- Delete actions remove only the selected records and leave unrelated records intact.

### Create page

- A valid form creates the model, assigns the authenticated tenant, applies defaults, generates identifiers, notifies, and redirects as expected.
- Required, type, maximum-length, conditional, and cross-field validation rejects invalid input without persisting a record or showing success notification.
- Unauthorized users and users without the required tenant cannot create records.
- Forged hidden, disabled, or protected fields such as `tenant_id`, `wedding_id`, status, counters, and UUIDs cannot change ownership or protected state.

### View page

- Direct fields, computed values, counts, URLs, metadata, timestamps, and boolean states are correct.
- Relation managers are present and scoped to the owner record.
- Records from another tenant result in the expected not-found/forbidden behavior.

### Edit page

- Editable fields persist valid changes and show the expected notification.
- The same validation and tenant-isolation rules as creation apply.
- Disabled or `dehydrated(false)` fields remain unchanged when a forged value is submitted.
- Conditional controls are tested in every state, including zero, one, and many related records.
- Toggle, share, delete, and other page actions are tested in both reversible/initial states where applicable.
- Deletion verifies the target model and relevant cascade, nullification, or pivot cleanup behavior.

### Relation managers

- The relation manager shows only records belonging to its owner resource.
- Create, edit, delete, bulk delete, validation, and cross-owner tampering are covered when supported.
- Relationship changes are asserted in the database/model state.

### Events and real-time tables

When a table listens for broadcasts, test all three contracts:

1. The Livewire listener map contains the exact channel and event name, including the leading `.` when `broadcastAs()` is used.
2. Calling the listener (for example, `refreshTable`) dispatches `$refresh` and renders the updated record state.
3. The broadcast event implements `ShouldBroadcast` and exposes the exact channel type/name, event name, and minimal payload.

## Hacker-Oriented Edge Cases

For every feature, act as a hacker trying to break the system. Attempt to:

- access, edit, delete, or search another tenant's record by guessing or replacing IDs/UUIDs;
- access another user's API resource or invoke an endpoint without the required authentication/authorization;
- submit another tenant's foreign key through hidden form state or mass assignment;
- forge disabled, hidden, non-dehydrated, read-only, counter, status, or UUID fields;
- bypass conditional UI rules by injecting values directly into Livewire form state;
- submit missing, oversized, wrong-type, malformed, duplicated, or contradictory values;
- replay a state-changing request, exceed rate limits, or exploit an idempotency gap;
- inject HTML/script content, unexpected files, unknown JSON keys, or invalid nested arrays;
- invoke row, bulk, relation-manager, or page actions outside their intended scope;
- access the panel unauthenticated or with the wrong user/team type;
- inject a globally hidden related item into a tenant-scoped pivot or visibility list;
- trigger a broadcast or event with another tenant's identifiers or an incomplete payload.

Every attempted attack must have a test that proves the system rejects it, preserves ownership, or leaves unrelated data unchanged. Prefer model/database assertions over checking only that the UI hides the value.

## Pest, HTTP, and Filament Assertions

Prefer behavior-specific assertions:

- `get()`, `post()`, `getJson()`, `postJson()`, and named `route()` helpers for HTTP behavior;
- `assertSuccessful()`, `assertCreated()`, `assertRedirect()`, `assertUnprocessable()`, `assertForbidden()`, `assertNotFound()`, and `assertTooManyRequests()`;
- `assertJson()`, `assertJsonPath()`, `assertJsonStructure()`, `assertInertia()`, and session assertions for response contracts;
- `Livewire::test(...)`, `fillForm()`, `call()`, `callAction()`, and `assertHasNoFormErrors()`;
- `assertCanSeeTableRecords()` and `assertCanNotSeeTableRecords()` for table scope;
- `assertFormFieldDisabled()`, `assertFormFieldEnabled()`, `assertFormFieldHidden()`, and schema-state assertions for conditional fields;
- `assertNotified()`, `assertRedirect()`, `assertDispatched('$refresh')`, and `assertSee()` where applicable;
- `assertModelExists()` and `assertModelMissing()` for persistence and deletion;

Use factory states and model assertions instead of raw database assertions when an equivalent assertion exists. Use `Str::repeat()` or datasets for boundary values. Do not weaken or skip a failing test; fix the implementation or the test fixture.

## Verification

Run the smallest affected test file first, then all tests in the affected HTTP or panel/resource subtree, and finally the full suite when practical:

```text
php artisan test --compact tests/Feature/Pages/{Context}
php artisan test --compact tests/Feature/API/{Context}
php artisan test --compact tests/Feature/Filament/{Panel}/{Resource}
php artisan test --compact
```

Run `vendor/bin/pint --dirty --format agent` when PHP test files or setup are changed. A feature-test change is complete only when the relevant tests and the full suite pass.