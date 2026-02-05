# Multi-Channel Authentication API

This document describes the new multi-channel authentication features added to the RestAPI module, supporting SMS, WhatsApp, and Telegram authentication methods.

## Overview

The multi-channel authentication system allows users (both customers and admins) to authenticate using various communication channels instead of traditional email/password authentication. This provides enhanced security and user convenience.

## Features

- **SMS Authentication**: Send verification codes via SMS
- **WhatsApp Authentication**: Send verification codes via WhatsApp Business API
- **Telegram Authentication**: Send verification codes via Telegram Bot API
- **Token Reset**: Reset authentication tokens via any supported channel
- **Verification Management**: Secure code generation and validation with attempt limits

## API Endpoints

### Customer Authentication

#### SMS Authentication
- **POST** `/api/v1/customer/auth/sms/initiate` - Initiate SMS authentication
- **POST** `/api/v1/customer/auth/verify` - Verify SMS code and authenticate

#### WhatsApp Authentication
- **POST** `/api/v1/customer/auth/whatsapp/initiate` - Initiate WhatsApp authentication
- **POST** `/api/v1/customer/auth/verify` - Verify WhatsApp code and authenticate

#### Telegram Authentication
- **POST** `/api/v1/customer/auth/telegram/initiate` - Initiate Telegram authentication
- **POST** `/api/v1/customer/auth/verify` - Verify Telegram code and authenticate

#### Token Reset
- **POST** `/api/v1/customer/auth/reset-token` - Request token reset via any channel
- **POST** `/api/v1/customer/auth/verify-reset` - Verify reset code and generate new token

### Admin Authentication

#### SMS Authentication
- **POST** `/api/v1/admin/auth/sms/initiate` - Initiate SMS authentication for admin
- **POST** `/api/v1/admin/auth/verify` - Verify SMS code and authenticate admin

#### WhatsApp Authentication
- **POST** `/api/v1/admin/auth/whatsapp/initiate` - Initiate WhatsApp authentication for admin
- **POST** `/api/v1/admin/auth/verify` - Verify WhatsApp code and authenticate admin

#### Telegram Authentication
- **POST** `/api/v1/admin/auth/telegram/initiate` - Initiate Telegram authentication for admin
- **POST** `/api/v1/admin/auth/verify` - Verify Telegram code and authenticate admin

#### Token Reset
- **POST** `/api/v1/admin/auth/reset-token` - Request admin token reset via any channel
- **POST** `/api/v1/admin/auth/verify-reset` - Verify reset code and generate new admin token

## Request/Response Examples

### SMS Authentication Flow

#### 1. Initiate SMS Authentication
```json
POST /api/v1/customer/auth/sms/initiate
{
    "phone_number": "1234567890",
    "country_code": "+1",
    "device_name": "iPhone 13"
}
```

**Response:**
```json
{
    "message": "Verification code sent to your phone.",
    "verification_token": "abc123def456",
    "expires_in": 600
}
```

#### 2. Verify Code and Authenticate
```json
POST /api/v1/customer/auth/verify
{
    "verification_code": "123456",
    "verification_token": "abc123def456",
    "device_name": "iPhone 13"
}
```

**Response:**
```json
{
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "phone": "+11234567890"
    },
    "message": "Authentication successful.",
    "token": "1|abc123def456..."
}
```

### Token Reset Flow

#### 1. Request Token Reset
```json
POST /api/v1/customer/auth/reset-token
{
    "reset_method": "sms",
    "phone_number": "+11234567890"
}
```

**Response:**
```json
{
    "message": "Verification code sent via sms.",
    "verification_token": "xyz789abc123",
    "expires_in": 600
}
```

#### 2. Verify Reset Code
```json
POST /api/v1/customer/auth/verify-reset
{
    "verification_code": "654321",
    "verification_token": "xyz789abc123"
}
```

**Response:**
```json
{
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "phone": "+11234567890"
    },
    "message": "Token reset successful.",
    "token": "2|xyz789abc123..."
}
```

## Security Features

### Verification Code Management
- **Configurable code length**: Numeric verification codes with configurable length (4-10 digits, default: 6). Code length can be configured separately for each authentication channel (SMS, WhatsApp, Telegram) in the admin panel.
- **10-minute expiration**: Codes expire after 10 minutes
- **3 attempt limit**: Maximum 3 verification attempts per code
- **Token-based verification**: Secure token system prevents replay attacks

### Rate Limiting
- Verification codes are rate-limited to prevent abuse
- Failed attempts are tracked and logged
- Suspicious activity triggers additional security measures

### Data Protection
- Phone numbers and Telegram IDs are validated before processing
- Verification data is stored temporarily in cache
- All sensitive data is cleaned up after successful verification

## Configuration

### Required Environment Variables
```env
# SMS Service (Twilio example)
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=+1234567890

# WhatsApp Business API
WHATSAPP_TOKEN=your_whatsapp_token
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id

# Telegram Bot API
TELEGRAM_BOT_TOKEN=your_telegram_bot_token
```

### Cache Configuration
The verification system uses Laravel's cache system. Ensure your cache driver is properly configured:

```env
CACHE_DRIVER=redis  # Recommended for production
# or
CACHE_DRIVER=memcached
```

## Database Requirements

### Customer Table Updates
Add the following columns to your customers table:
```sql
ALTER TABLE customers ADD COLUMN phone VARCHAR(20) NULL;
ALTER TABLE customers ADD COLUMN telegram_id VARCHAR(255) NULL;
ALTER TABLE customers ADD COLUMN whatsapp_id VARCHAR(255) NULL;
```

### Admin Table Updates
Add the following columns to your admins table:
```sql
ALTER TABLE admins ADD COLUMN phone VARCHAR(20) NULL;
ALTER TABLE admins ADD COLUMN telegram_id VARCHAR(255) NULL;
ALTER TABLE admins ADD COLUMN whatsapp_id VARCHAR(255) NULL;
```

## Error Handling

### Common Error Responses

#### Invalid Phone Number Format
```json
{
    "message": "Invalid phone number format."
}
```

#### No Account Found
```json
{
    "message": "No account found with this phone number."
}
```

#### Invalid Verification Code
```json
{
    "message": "Invalid or expired verification code."
}
```

#### Failed to Send Code
```json
{
    "message": "Failed to send verification code."
}
```

## Integration Examples

### SMS Service Integration (Twilio)
```php
// In SmsService.php
public function sendVerificationCode(string $phoneNumber, string $code): bool
{
    try {
        $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
        $twilio->messages->create($phoneNumber, [
            'from' => config('services.twilio.from'),
            'body' => "Your verification code is: {$code}"
        ]);
        return true;
    } catch (\Exception $e) {
        Log::error("SMS sending failed: " . $e->getMessage());
        return false;
    }
}
```

### WhatsApp Service Integration
```php
// In WhatsAppService.php
public function sendVerificationCode(string $phoneNumber, string $code): bool
{
    try {
        $whatsappApi = new WhatsAppApi(config('services.whatsapp.token'));
        $whatsappApi->sendMessage($phoneNumber, "Your verification code is: {$code}");
        return true;
    } catch (\Exception $e) {
        Log::error("WhatsApp sending failed: " . $e->getMessage());
        return false;
    }
}
```

### Telegram Service Integration
```php
// In TelegramService.php
public function sendVerificationCode(string $telegramId, string $code): bool
{
    try {
        $telegramBot = new TelegramBot(config('services.telegram.bot_token'));
        $telegramBot->sendMessage($telegramId, "Your verification code is: {$code}");
        return true;
    } catch (\Exception $e) {
        Log::error("Telegram sending failed: " . $e->getMessage());
        return false;
    }
}
```

## Testing

### Unit Tests
Run the test suite to ensure all functionality works correctly:
```bash
php artisan test --filter=MultiChannelAuth
```

### Manual Testing
1. Test SMS authentication with a valid phone number
2. Test WhatsApp authentication with a valid phone number
3. Test Telegram authentication with a valid Telegram ID
4. Test token reset functionality
5. Test error handling for invalid inputs

## Troubleshooting

### Common Issues

1. **Verification codes not being sent**
   - Check service provider credentials
   - Verify phone number format
   - Check service provider quotas and limits

2. **Codes expiring too quickly**
   - Verify cache configuration
   - Check system time synchronization

3. **Invalid verification errors**
   - Check code format (must be numeric, 4-10 digits, length configured per channel)
   - Verify token hasn't expired
   - Check attempt limits

## Support

For technical support or questions about the multi-channel authentication system, please refer to the API documentation or contact the development team.
