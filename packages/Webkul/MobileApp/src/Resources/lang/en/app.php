<?php

return [
    'settings' => [
        'title'        => 'Mobile App',
        'info'         => 'Configure settings for mobile application',
        'save-success' => 'Settings saved successfully',

        'general' => [
            'title' => 'General',
            'info'  => 'General mobile app settings',

            'app-info' => [
                'title' => 'App Information',
                'info'  => 'Basic application information',
            ],

            'custom' => [
                'title' => 'Custom Settings',
                'info'  => 'Custom configuration data',
            ],

            'contact-us' => [
                'title' => 'Contact Us',
                'info'  => 'Contact information for mobile app',
            ],

            'documents' => [
                'title' => 'Document Links',
                'info'  => 'Links to documents for mobile app',
            ],
        ],

        'fields' => [
            'app-name'              => 'App Name',
            'app-name-info'         => 'Name of the mobile application',
            'app-version'           => 'App Version',
            'app-version-info'      => 'Current version of the app',
            'min-app-version'       => 'Minimum App Version',
            'min-app-version-info'  => 'Minimum required version',
            'force-update'          => 'Force Update',
            'force-update-info'     => 'Force users to update the app',
            'maintenance-mode'      => 'Maintenance Mode',
            'maintenance-mode-info' => 'Enable maintenance mode for mobile app',
            'custom-data'           => 'Custom Data (JSON)',
            'custom-data-info'      => 'Custom JSON data for mobile app',

            'featured-categories'      => 'Featured Categories',
            'featured-categories-info' => 'Select categories to feature in mobile app',
            'featured-products'        => 'Featured Products',
            'featured-products-info'   => 'Select products to feature in mobile app',
            'default-channel'          => 'Default Channel',
            'default-channel-info'     => 'Default sales channel for mobile app',
            'shipping-methods'         => 'Shipping Methods',
            'shipping-methods-info'    => 'Available shipping methods in mobile app',
            'home-filters'             => 'Home Screen Filters',
            'home-filters-info'        => 'Select attributes to use as filters on home screen',
            'contact-telegram'         => 'Telegram Contact',
            'contact-telegram-info'    => 'Telegram contact link',
            'contact-whatsapp'         => 'WhatsApp Contact',
            'contact-whatsapp-info'    => 'WhatsApp contact link',
            'contact-email'            => 'Email',
            'contact-email-info'       => 'Email address',
            'contact-max'              => 'Max Messenger Contact',
            'contact-max-info'         => 'Max Messenger contact link',
            'user-agreement'            => 'User Agreement',
            'user-agreement-info'       => 'Select CMS page for user agreement',
            'privacy-policy'            => 'Privacy Policy',
            'privacy-policy-info'       => 'Select CMS page for privacy policy',

            'push-enabled'                     => 'Enable Push Notifications',
            'push-enabled-info'                => 'Enable push notifications sending through Firebase',
            'firebase-credentials-json'        => 'Firebase Credentials (JSON)',
            'firebase-credentials-json-info'   => 'Copy the contents of the Service Account JSON file from Firebase Console',
            'firebase-project-id'              => 'Firebase Project ID',
            'firebase-project-id-info'         => 'Project ID from Firebase (found in Service Account JSON)',
            'push-statuses'                    => 'Statuses for Push Notifications',
            'push-statuses-info'               => 'Select order statuses that trigger push notifications',
            'push-info'                        => 'May contain placeholders: {order_id}, {status_label}',
            'push-body-info'                   => 'Full notification text (may contain placeholders: {order_id}, {status_label})',

            'push-title-pending'               => 'Title for "Pending" status',
            'push-body-pending'                => 'Text for "Pending" status',
            'push-title-pending-payment'       => 'Title for "Payment Pending" status',
            'push-body-pending-payment'        => 'Text for "Payment Pending" status',
            'push-title-processing'            => 'Title for "Processing" status',
            'push-body-processing'             => 'Text for "Processing" status',
            'push-title-preparing'             => 'Title for "Preparing" status',
            'push-body-preparing'              => 'Text for "Preparing" status',
            'push-title-ready'                 => 'Title for "Ready" status',
            'push-body-ready'                  => 'Text for "Ready" status',
            'push-title-completed'             => 'Title for "Completed" status',
            'push-body-completed'              => 'Text for "Completed" status',
            'push-title-canceled'              => 'Title for "Canceled" status',
            'push-body-canceled'               => 'Text for "Canceled" status',
            'push-title-closed'                => 'Title for "Closed" status',
            'push-body-closed'                 => 'Text for "Closed" status',
        ],

        'push-notifications' => [
            'title' => 'Push Notifications',
            'info'  => 'Configure push notifications sending through Firebase Cloud Messaging',

            'settings' => [
                'title' => 'Firebase Settings',
                'info'  => 'Basic Firebase configuration parameters',
            ],

            'messages' => [
                'title' => 'Notification Messages',
                'info'  => 'Configure notification text for each order status',
            ],
        ],

        'order-status' => [
            'pending'           => 'Pending',
            'pending-payment'   => 'Payment Pending',
            'processing'        => 'Processing',
            'preparing'         => 'Preparing',
            'ready'             => 'Ready',
            'completed'         => 'Completed',
            'canceled'          => 'Canceled',
            'closed'            => 'Closed',
        ],
    ],

    'acl' => [
        'settings' => 'Mobile App Settings',
        'edit'     => 'Edit',
    ],
];

