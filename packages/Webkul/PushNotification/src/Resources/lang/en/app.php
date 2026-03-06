<?php

return [
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
