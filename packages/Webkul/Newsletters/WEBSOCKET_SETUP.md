# WebSocket Broadcasting Implementation for Mailing Lists

This implementation provides real-time updates for the `sent_count` and `incoming_count` columns on the `/admin/newsletters/mailing-lists` page using Laravel Reverb WebSocket broadcasting.

## Features

- **Real-time Updates**: Automatically updates `sent_count` and `incoming_count` when data changes in the database
- **WebSocket Broadcasting**: Uses Laravel Reverb for efficient real-time communication
- **Webhook Integration**: Processes GreenAPI webhooks and broadcasts updates
- **Visual Feedback**: Shows notifications and highlights updated rows
- **Error Handling**: Comprehensive logging and error management

## Architecture

### Components Created

1. **MailingListStatsUpdated Event** (`src/Events/MailingListStatsUpdated.php`)
   - Broadcasts mailing list statistics updates
   - Queued for better performance
   - Includes timestamp and mailing list ID

2. **CustomerNumberObserver** (`src/Observers/CustomerNumberObserver.php`)
   - Monitors CustomerNumber model changes
   - Triggers broadcasts when `delivered`, `incoming_message`, or `viewed` status changes
   - Calculates real-time statistics

3. **Enhanced HooksController** (`src/Http/Controllers/Admin/HooksController.php`)
   - Processes GreenAPI webhooks
   - Handles different webhook types (incoming messages, delivery status, read receipts)
   - Updates database and broadcasts changes

4. **Updated Frontend** (`src/Resources/views/admin/mailing-lists/index.blade.php`)
   - WebSocket client using Pusher.js
   - Real-time UI updates
   - Visual feedback and notifications

## Setup Instructions

### 1. Environment Configuration

Add these variables to your `.env` file:

```env
# Broadcasting Configuration
BROADCAST_CONNECTION=reverb

# Reverb Configuration
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_CLUSTER=mt1
```

### 2. Start Reverb Server

```bash
# Start the Reverb WebSocket server
php artisan reverb:start

# Or run in background
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### 3. Start Queue Worker

```bash
# Start queue worker for broadcasting
php artisan queue:work --queue=broadcastable
```

### 4. Configure GreenAPI Webhooks

Set your webhook URL in GreenAPI to:
```
https://yourdomain.com/admin/newsletters/hook/webhook
```

## Usage

### Automatic Updates

The system automatically updates the mailing list statistics when:

- **Messages are delivered**: Updates `sent_count`
- **Incoming messages received**: Updates `incoming_count`
- **Messages are read**: Updates `viewed_count`
- **Customer numbers are modified**: Recalculates all statistics

### Manual Testing

Use the test command to verify WebSocket broadcasting:

```bash
php artisan newsletters:test-broadcast {mailing_list_id}
```

Example:
```bash
php artisan newsletters:test-broadcast 1
```

### Webhook Processing

The system processes these GreenAPI webhook types:

- `incomingMessageReceived`: Updates `incoming_message` status
- `outgoingMessageStatus`: Updates `delivered` status
- `delivered`: Confirms message delivery
- `read`: Updates `viewed` status

## Frontend Features

### Real-time Updates
- Automatically updates table cells when data changes
- Visual highlighting of updated rows
- Toast notifications for user feedback

### WebSocket Connection
- Connects to Reverb server using Pusher.js
- Handles connection errors gracefully
- Shows connection status in console

### Visual Feedback
- Row highlighting when updated
- Smooth transitions
- Notification system

## Database Schema

The system uses these fields in the `newsletters_customer_numbers` table:

- `delivered` (boolean): Message delivery status
- `incoming_message` (boolean): Incoming message status
- `viewed` (boolean): Message read status
- `mailing_list_id` (foreign key): Links to mailing list

## Monitoring and Logging

### Logs
All activities are logged with detailed information:

```php
Log::info('Mailing list stats updated', [
    'mailing_list_id' => $mailingListId,
    'stats' => $stats
]);
```

### Console Commands
- `newsletters:test-broadcast`: Test WebSocket broadcasting
- `reverb:start`: Start Reverb server
- `queue:work --queue=broadcastable`: Process broadcast queue

## Troubleshooting

### Common Issues

1. **WebSocket Connection Failed**
   - Check if Reverb server is running
   - Verify environment configuration
   - Check firewall settings

2. **No Real-time Updates**
   - Ensure queue worker is running
   - Check browser console for errors
   - Verify webhook URL configuration

3. **Database Updates Not Broadcasting**
   - Check observer registration
   - Verify model events are firing
   - Check queue processing

### Debug Commands

```bash
# Check Reverb server status
php artisan reverb:restart

# Test broadcasting
php artisan newsletters:test-broadcast 1

# Check queue status
php artisan queue:work --queue=broadcastable --verbose
```

## Performance Considerations

- **Queued Broadcasting**: Events are queued to prevent blocking
- **Efficient Queries**: Uses optimized SQL queries for statistics
- **Rate Limiting**: Prevents excessive broadcasts
- **Connection Management**: Handles WebSocket connections efficiently

## Security

- **CSRF Protection**: All webhook endpoints are protected
- **Input Validation**: Webhook data is validated
- **Error Handling**: Prevents information leakage
- **Logging**: Comprehensive audit trail

## Future Enhancements

- **User-specific Channels**: Private channels for admin users
- **Batch Updates**: Group multiple updates together
- **Metrics Dashboard**: Real-time analytics
- **Mobile Notifications**: Push notifications for critical updates
