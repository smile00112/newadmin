# Environment Configuration Guide

## Required Environment Variables

Add these variables to your `.env` file to enable WebSocket broadcasting:

```env
# Broadcasting Configuration
BROADCAST_CONNECTION=reverb

# Reverb Configuration (Laravel's WebSocket server)
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_CLUSTER=mt1

# Queue Configuration (for broadcasting)
QUEUE_CONNECTION=redis
REDIS_QUEUE=default
REDIS_QUEUE_RETRY_AFTER=90

# Redis Configuration (if using Redis for queues)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
```

## Quick Setup Commands

1. **Generate Reverb keys:**
```bash
php artisan reverb:install
```

2. **Start Reverb server:**
```bash
php artisan reverb:start
```

3. **Start queue worker:**
```bash
php artisan queue:work --queue=broadcastable
```

4. **Test the implementation:**
```bash
php artisan newsletters:test-broadcast 1
```

## Webhook Configuration

Set your GreenAPI webhook URL to:
```
https://yourdomain.com/admin/newsletters/hook/webhook
```

## Verification

1. Open `/admin/newsletters/mailing-lists` in your browser
2. Open browser console to see WebSocket connection status
3. Run the test command to verify broadcasting works
4. Check that table cells update in real-time
