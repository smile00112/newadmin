<?php

return [
    'apple-live-activity' => [
        'title' => 'Apple Live Activity',
        'info'  => 'Push notifications for Apple Live Activity (lock-screen widget)',

        'settings' => [
            'title' => 'APNs Settings',
            'info'  => 'Apple Push Notification service configuration for Live Activity',
        ],

        'fields' => [
            'enabled'      => 'Enable Apple Live Activity',
            'enabled-info' => 'Send Live Activity updates to iOS via APNs when the order status changes',
            'sandbox'      => 'Sandbox Mode',
            'sandbox-info' => 'Use APNs sandbox (api.sandbox.push.apple.com). Disable for production.',
            'bundle-id'    => 'App Bundle ID',
            'bundle-id-info' => 'iOS app bundle identifier (e.g. com.example.app)',
            'team-id'      => 'Apple Team ID',
            'team-id-info' => '10-character Team ID from Apple Developer portal',
            'key-id'       => 'APNs Key ID',
            'key-id-info'  => '10-character Key ID for the .p8 authentication key',
            'p8-key'       => '.p8 Private Key',
            'p8-key-info'  => 'Paste the full content of the AuthKey_XXXXXXXX.p8 file or provide an absolute path to the file',
        ],
    ],

    'settings' => [
        'title' => 'Push Notifications',
        'info'  => 'Push notification settings via Firebase Cloud Messaging',

        'general' => [
            'title' => 'General',
            'info'  => 'General push notification settings',

            'settings' => [
                'title' => 'Firebase Settings',
                'info'  => 'Firebase connection parameters',
            ],

            'messages' => [
                'title' => 'Notification Messages',
                'info'  => 'Configure notification text for each order status',
            ],
        ],

        'fields' => [
            'enabled'                        => 'Enable Push Notifications',
            'enabled-info'                   => 'Enable push notification sending through Firebase',
            'firebase-credentials-json'      => 'Firebase Credentials (JSON)',
            'firebase-credentials-json-info' => 'Copy the contents of the Service Account JSON file from Firebase Console',
            'firebase-project-id'            => 'Firebase Project ID',
            'firebase-project-id-info'       => 'Project ID from Firebase (found in Service Account JSON)',
            'statuses'                       => 'Statuses for Push Notifications',
            'statuses-info'                  => 'Select order statuses that trigger push notifications',
            'placeholder-info'               => 'Supports placeholders: {order_id}, {status_label}',

            'title-pending'         => 'Title — "Pending"',
            'body-pending'          => 'Text — "Pending"',
            'title-pending-payment' => 'Title — "Payment Pending"',
            'body-pending-payment'  => 'Text — "Payment Pending"',
            'title-processing'      => 'Title — "Processing"',
            'body-processing'       => 'Text — "Processing"',
            'title-preparing'       => 'Title — "Preparing"',
            'body-preparing'        => 'Text — "Preparing"',
            'title-ready'           => 'Title — "Ready"',
            'body-ready'            => 'Text — "Ready"',
            'title-completed'       => 'Title — "Completed"',
            'body-completed'        => 'Text — "Completed"',
            'title-canceled'        => 'Title — "Canceled"',
            'body-canceled'         => 'Text — "Canceled"',
            'title-closed'          => 'Title — "Closed"',
            'body-closed'           => 'Text — "Closed"',
        ],

        'order-status' => [
            'pending'         => 'Pending',
            'pending-payment' => 'Payment Pending',
            'processing'      => 'Processing',
            'preparing'       => 'Preparing',
            'ready'           => 'Ready',
            'completed'       => 'Completed',
            'canceled'        => 'Canceled',
            'closed'          => 'Closed',
        ],
    ],
];
