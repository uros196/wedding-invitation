---
name: translation-development
description: Defines how translations are created, organized, referenced, and verified in this Laravel application. Use when adding or changing any user-facing text, including Filament labels, helpers, validation messages, notifications, widgets, Blade, and JavaScript.
license: MIT
metadata:
  author: project
---

# Translation Development Guide

This application has two supported locales: `en` and `sr_Latn`. Every static string shown to a user must be defined in translation resources. Never add user-facing English text directly to PHP classes, Blade templates, JavaScript, configuration used for display, validation rules, notifications, or widget definitions.

## Translation resources

Use the following canonical locations:

| Resource                     | Use for                                                                                                                               | Example                                |
|------------------------------|---------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------|
| `lang/{locale}.json`         | Short, reusable labels, actions, statuses, values, and metadata                                                                       | `Save Changes`, `Guests`, `Created At` |
| `lang/{locale}/wedding.php`  | Long text and Wedding-specific descriptions, helpers, placeholders, empty states, validations, widget descriptions, and notifications | `wedding.groups.plus_one.description`  |
| `lang/{locale}/messages.php` | Messages that are genuinely universal and can be used outside the Wedding domain                                                      | `messages.link_copied`                 |

The current Wedding structure in `wedding.php` is:

```text
title
greeting
manage_wedding
groups
guests
widgets
notifications
```

Use `lower_snake_case` for PHP translation keys and group them by feature, not by the name of a component class. Keep `title` and `greeting` at the root of `wedding.php`.

### JSON translations

JSON is the canonical resource for short expressions that can naturally be reused across panels. Reuse an existing key before creating a new one. JSON keys are the source-language strings, including their exact capitalization and punctuation:

```json
{
    "Guests": "Gosti",
    "Save Changes": "Sačuvaj izmene",
    "UUID": "UUID"
}
```

Short Wedding UI terms such as `Timeline`, `Memory Wall`, and `Wedding Details` remain in JSON when they have clear reuse value. Length alone is not the deciding criterion; reuse is.

### `wedding.php` translations

Put long or domain-specific text in a nested PHP resource. Add the same key structure to both locales:

```php
// lang/en/wedding.php
'groups' => [
    'plus_one' => [
        'description' => 'Allow the guest to add a plus one.',
    ],
],
```

```php
__('wedding.groups.plus_one.description')
```

Use `wedding.manage_wedding`, `wedding.groups`, `wedding.guests`, `wedding.widgets`, or `wedding.notifications` for new Wedding translations. Do not place long helper text, placeholders, empty states, or validation text in JSON.

### `messages.php` translations

Use `messages.php` only when the message has no Wedding meaning and can be reused anywhere in the application. The existing universal `messages.link_copied` entry remains there. Do not keep a Wedding key in this file merely because it already exists there; move it to `wedding.php` or JSON according to the rules above.

Do not add legacy aliases for moved keys. When correcting a translation key, update all consumers in the same change. The attendance confirmation key is canonicalized as:

```text
wedding.notifications.attendance_confirmation_success
```

## Referencing translations

Use Laravel's `__()` helper in PHP, Filament schemas, actions, resources, widgets, controllers, and Blade:

```php
TextInput::make('notes')
    ->label(__('Notes'))
    ->placeholder(__('wedding.guests.form.notes_placeholder'));
```

Use translation keys rather than literal display text in every user-facing property, including:

- labels, headings, field names, table columns, filters, and actions;
- helper text, placeholders, empty states, and confirmation dialogs;
- validation and authorization messages;
- notification titles and flash messages;
- widget headings, dataset labels, and descriptions;
- text rendered by Blade or inline JavaScript.

Do not translate values entered by users, database content, or technical identifiers unless they are static display labels. Do not use `config('app.name')` as a substitute for a translation when the displayed value is a translatable application string.

### JavaScript and escaping

Inline JavaScript must also use translation resources. Preserve the escaping required by the surrounding context. For the existing universal message, keep the established pattern and its `addslashes` handling:

```php
const linkCopiedMessage = "{{ addslashes(__('messages.link_copied')) }}";
```

When moving a string used by JavaScript, change only its translation lookup and preserve the existing escaping and behavior.

### Parameters and interpolation

Keep all placeholders identical in `en` and `sr_Latn`, and pass values through Laravel's translation call:

```php
__('wedding.guests.summary', ['count' => $count])
```

Never concatenate a translated sentence from hardcoded fragments when one complete translatable sentence is possible.

## Adding or changing a translation

Follow this checklist:

1. Search the whole project for an existing equivalent key or source string and reuse the canonical entry.
2. Classify the text as reusable short text (JSON), Wedding-specific/long text (`wedding.php`), or universal system message (`messages.php`).
3. Add or update the key in both `lang/en` and `lang/sr_Latn` resources with identical structure.
4. Replace every literal user-facing string and every obsolete reference in the affected consumers.
5. Check inline JavaScript, Blade, interpolation, and escaping separately.
6. Confirm that no static string remains in application code and that no moved `messages.*` or long literal `__()` reference remains.
7. Validate PHP syntax, JSON validity, and recursive key parity between locales.

For a migration, use JSON as the canonical source for short duplicates, move long Wedding text into the appropriate `wedding.php` group, retain only genuinely universal `messages.php` entries, and remove obsolete keys only after all references have been migrated.

## Verification checklist

Before completing a translation change, verify:

- both locale files contain every new key;
- `en` and `sr_Latn` have identical recursive key structures;
- JSON files are valid and PHP language files pass syntax checks;
- all user-facing static strings use a translation lookup;
- no deleted or legacy translation key is referenced;
- placeholders and escaping are unchanged;
- the diff contains only the intended translation resources and their references.
