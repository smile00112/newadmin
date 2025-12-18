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
        ],
    ],

    'acl' => [
        'settings' => 'Mobile App Settings',
        'edit'     => 'Edit',
    ],
];

