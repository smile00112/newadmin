# Newsletters Module

This module provides comprehensive newsletter management functionality for the Laravel application, including Vacap instances management, customer number management, stop list functionality, and mailing list operations.

## Features

### 1. Vacap Instances Management
- Create and manage Vacap API instances
- Configure API URLs, keys, and secrets
- Enable/disable instances
- Store additional settings and descriptions

### 2. Customer Numbers Management
- Manage customer phone numbers and contact information
- Associate customers with mailing lists
- Track subscription status and dates
- Import customer numbers from CSV files
- Store metadata for each customer

### 3. Stop List Management
- Block phone numbers from receiving newsletters
- Track blocking reasons and admin who blocked
- Add notes for blocked numbers
- Check if phone numbers are blocked

### 4. Mailing Lists Management
- Create and manage newsletter campaigns
- Schedule newsletters for future sending
- Track sending status and statistics
- Associate with Vacap instances
- Monitor sent/failed counts

## Database Tables

### newsletters_whatsapp_instances
- `id` - Primary key
- `name` - Instance name
- `api_url` - API endpoint URL
- `api_key` - API authentication key
- `api_secret` - API secret (optional)
- `is_active` - Active status
- `description` - Instance description
- `settings` - JSON settings
- `created_at`, `updated_at` - Timestamps

### newsletters_customer_numbers
- `id` - Primary key
- `phone_number` - Customer phone number
- `name` - Customer name (optional)
- `email` - Customer email (optional)
- `mailing_list_id` - Foreign key to mailing_lists
- `is_active` - Active status
- `subscribed_at` - Subscription timestamp
- `unsubscribed_at` - Unsubscription timestamp
- `metadata` - JSON metadata
- `created_at`, `updated_at` - Timestamps

### newsletters_stop_list
- `id` - Primary key
- `phone_number` - Blocked phone number
- `reason` - Blocking reason
- `blocked_at` - Blocking timestamp
- `blocked_by` - Admin who blocked (foreign key)
- `notes` - Additional notes
- `created_at`, `updated_at` - Timestamps

### newsletters_mailing_lists
- `id` - Primary key
- `name` - Mailing list name
- `description` - List description
- `whatsapp_instance_id` - Foreign key to whatsapp_instances
- `message_text` - Newsletter message content
- `status` - Status (draft, scheduled, sending, sent, failed)
- `scheduled_at` - Scheduled sending time
- `sent_at` - Actual sending time
- `total_recipients` - Total recipient count
- `sent_count` - Successfully sent count
- `failed_count` - Failed sending count
- `settings` - JSON settings
- `created_by` - Admin who created (foreign key)
- `created_at`, `updated_at` - Timestamps

## Admin Interface

### Menu Structure
The module adds a "Newsletters" menu item to the admin sidebar with the following sub-items:
- Vacap Instances
- Mailing Lists
- Customer Numbers
- Stop List

### Controllers
- `VacapInstanceController` - Manage Vacap instances
- `MailingListController` - Manage mailing lists and sending
- `CustomerNumberController` - Manage customer numbers and imports
- `StopListController` - Manage blocked phone numbers

### DataGrids
- `MailingListDataGrid` - Display mailing lists with filtering and actions

## Configuration

### Environment Variables
```env
NEWSLETTERS_DEFAULT_VACAP_INSTANCE=1
NEWSLETTERS_SMS_PER_MINUTE=60
NEWSLETTERS_SMS_PER_HOUR=1000
NEWSLETTERS_SMS_PER_DAY=10000
NEWSLETTERS_MAX_RETRIES=3
NEWSLETTERS_RETRY_DELAY=60
NEWSLETTERS_MAX_MESSAGE_LENGTH=160
NEWSLETTERS_QUEUE_CONNECTION=default
NEWSLETTERS_QUEUE_NAME=newsletters
NEWSLETTERS_CACHE_TTL=3600
```

### Settings
The module includes comprehensive configuration options for:
- Rate limiting
- Retry settings
- Validation rules
- Queue configuration
- Cache settings

## API Endpoints

### Admin Routes
- `GET /admin/newsletters/whatsapp-instances` - List Vacap instances
- `POST /admin/newsletters/whatsapp-instances/create` - Create Vacap instance
- `PUT /admin/newsletters/whatsapp-instances/edit/{id}` - Update Vacap instance
- `DELETE /admin/newsletters/whatsapp-instances/{id}` - Delete Vacap instance

- `GET /admin/newsletters/mailing-lists` - List mailing lists
- `POST /admin/newsletters/mailing-lists/create` - Create mailing list
- `PUT /admin/newsletters/mailing-lists/edit/{id}` - Update mailing list
- `DELETE /admin/newsletters/mailing-lists/{id}` - Delete mailing list
- `POST /admin/newsletters/mailing-lists/{id}/send` - Send mailing list

- `GET /admin/newsletters/customer-numbers` - List customer numbers
- `POST /admin/newsletters/customer-numbers/create` - Create customer number
- `PUT /admin/newsletters/customer-numbers/edit/{id}` - Update customer number
- `DELETE /admin/newsletters/customer-numbers/{id}` - Delete customer number
- `POST /admin/newsletters/customer-numbers/import` - Import from CSV

- `GET /admin/newsletters/stop-list` - List blocked numbers
- `POST /admin/newsletters/stop-list/create` - Block phone number
- `PUT /admin/newsletters/stop-list/edit/{id}` - Update blocked number
- `DELETE /admin/newsletters/stop-list/{id}` - Unblock phone number
- `POST /admin/newsletters/stop-list/check` - Check if number is blocked

## Usage Examples

### Creating a Vacap Instance
```php
$whatsappInstance = app(\Webkul\Newsletters\Repositories\VacapInstanceRepository::class)->create([
    'name' => 'Main SMS Provider',
    'api_url' => 'https://api.whatsapp.com/sms',
    'api_key' => 'your-api-key',
    'api_secret' => 'your-api-secret',
    'is_active' => true,
    'description' => 'Primary SMS service provider',
]);
```

### Creating a Mailing List
```php
$mailingList = app(\Webkul\Newsletters\Repositories\MailingListRepository::class)->create([
    'name' => 'Weekly Newsletter',
    'description' => 'Weekly product updates',
    'whatsapp_instance_id' => 1,
    'message_text' => 'Check out our latest products!',
    'status' => 'draft',
    'created_by' => auth()->guard('admin')->id(),
]);
```

### Adding Customer Numbers
```php
$customerNumber = app(\Webkul\Newsletters\Repositories\CustomerNumberRepository::class)->create([
    'phone_number' => '+1234567890',
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'mailing_list_id' => 1,
    'is_active' => true,
    'subscribed_at' => now(),
]);
```

### Blocking Phone Numbers
```php
$stopList = app(\Webkul\Newsletters\Repositories\StopListRepository::class)->create([
    'phone_number' => '+1234567890',
    'reason' => 'Opted out',
    'blocked_at' => now(),
    'blocked_by' => auth()->guard('admin')->id(),
    'notes' => 'Customer requested to stop receiving messages',
]);
```

## Installation

1. Ensure the module is properly registered in your application's service providers
2. Run migrations: `php artisan migrate`
3. Clear cache: `php artisan config:clear`
4. Access the admin panel and navigate to the Newsletters section

## Security Features

- Phone number validation
- Rate limiting for API calls
- Admin authentication required for all operations
- Audit trail for blocked numbers
- Secure API key storage

## Future Enhancements

- Integration with actual Vacap API
- Advanced scheduling options
- Template management
- Analytics and reporting
- Bulk operations
- Webhook support  POST /newsletters/hook/webhook
- Multi-language support

## Support

For technical support or questions about the newsletters module, please refer to the documentation or contact the development team.


## Zametki
php artisan queue:work redis --queue=whatsapp-mailing,broadcastable,default,whatsapp_mailing,whatsapp-batch,whatsapp-send












