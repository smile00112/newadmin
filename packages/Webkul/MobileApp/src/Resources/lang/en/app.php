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

            'order-defaults' => [
                'title' => 'Default Order Methods',
                'info'  => 'Automatically assign shipping and payment methods when creating orders via mobile app, if not selected by user',
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

            'auto-assign-shipping'         => 'Auto-assign shipping method',
            'auto-assign-shipping-info'    => 'Apply default shipping when user has not selected one',
            'default-shipping-method'      => 'Default shipping method',
            'default-shipping-method-info' => 'Shipping method to use when auto-assign is enabled',
            'auto-assign-payment'          => 'Auto-assign payment method',
            'auto-assign-payment-info'     => 'Apply default payment when user has not selected one',
            'default-payment-method'       => 'Default payment method',
            'default-payment-method-info'  => 'Payment method to use when auto-assign is enabled',
        ],
    ],

    'acl' => [
        'settings' => 'Mobile App Settings',
        'edit'     => 'Edit',
    ],
];

