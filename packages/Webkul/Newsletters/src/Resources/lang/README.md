# Newsletters Module Translations

This directory contains translation files for the Newsletters module in multiple languages.

## Available Languages

- **English (en)** - Default language
- **Russian (ru)** - Russian language support

## Translation Files Structure

### Main Application Translations (`app.php`)

Contains all UI text for the newsletters module:

- **Admin Interface**: Titles, success/error messages, field labels
- **Sidebar Menu**: Navigation menu items
- **Common Elements**: Actions, messages, field labels used across the module

### Validation Messages (`validation.php`)

Contains validation error messages for form fields:

- Field validation rules (required, string, max, unique, etc.)
- Custom validation messages for newsletters-specific fields

### Pagination (`pagination.php`)

Contains pagination-related text:

- Previous/Next navigation
- Page information display
- Results count display

### Authentication (`auth.php`)

Contains authentication-related messages:

- Login/logout messages
- Password reset functionality
- Email verification messages

### Password Reset (`passwords.php`)

Contains password reset functionality messages:

- Password reset confirmation
- Email sending notifications
- Token validation messages

## Usage in Views

Use the translation helper functions in your Blade templates:

```php
// Basic translation
{{ __('newsletters::app.admin.mailing-lists.title') }}

// Translation with parameters
{{ __('newsletters::app.common.messages.success') }}

// Translation with fallback
{{ __('newsletters::app.admin.whatsapp-instances.title', [], 'en') }}
```

## Adding New Languages

To add support for a new language:

1. Create a new directory with the language code (e.g., `fr` for French)
2. Copy the English files to the new directory
3. Translate all the text values to the target language
4. Update the module service provider to register the new language

## Translation Keys Structure

```
newsletters::app.admin.{section}.{key}
newsletters::app.sidebar.{key}
newsletters::app.common.{category}.{key}
newsletters::validation.{key}
newsletters::pagination.{key}
newsletters::auth.{key}
newsletters::passwords.{key}
```

## Contributing Translations

When contributing translations:

1. Ensure all keys are present in all language files
2. Use proper grammar and terminology for the target language
3. Test the translations in the actual interface
4. Follow the existing naming conventions
5. Update this documentation if adding new translation categories



