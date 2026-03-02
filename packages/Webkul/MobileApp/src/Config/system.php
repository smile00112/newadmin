<?php

return [
    /**
     * Mobile App Settings.
     */
    [
        'key'  => 'mobile_app',
        'name' => 'mobile_app::app.settings.title',
        'info' => 'mobile_app::app.settings.info',
        'sort' => 10,
    ],
    [
        'key'  => 'mobile_app.general',
        'name' => 'mobile_app::app.settings.general.title',
        'info' => 'mobile_app::app.settings.general.info',
        'icon' => 'settings/store.svg',
        'sort' => 1,
    ],
    [
        'key'    => 'mobile_app.general.app_info',
        'name'   => 'mobile_app::app.settings.general.app-info.title',
        'info'   => 'mobile_app::app.settings.general.app-info.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'    => 'app_name',
                'title'   => 'mobile_app::app.settings.fields.app-name',
                'type'    => 'text',
                'default' => '',
            ],
            [
                'name'    => 'app_version',
                'title'   => 'mobile_app::app.settings.fields.app-version',
                'type'    => 'text',
                'default' => '1.0.0',
            ],
            [
                'name'    => 'min_app_version',
                'title'   => 'mobile_app::app.settings.fields.min-app-version',
                'type'    => 'text',
                'default' => '1.0.0',
            ],
            [
                'name'    => 'force_update',
                'title'   => 'mobile_app::app.settings.fields.force-update',
                'type'    => 'boolean',
                'default' => false,
            ],
            [
                'name'    => 'maintenance_mode',
                'title'   => 'mobile_app::app.settings.fields.maintenance-mode',
                'type'    => 'boolean',
                'default' => false,
            ],
        ],
    ],
    [
        'key'    => 'mobile_app.general.custom',
        'name'   => 'mobile_app::app.settings.general.custom.title',
        'info'   => 'mobile_app::app.settings.general.custom.info',
        'sort'   => 2,
        'fields' => [
            [
                'name'  => 'custom_data',
                'title' => 'mobile_app::app.settings.fields.custom-data',
                'info'  => 'mobile_app::app.settings.fields.custom-data-info',
                'type'  => 'textarea',
            ],
        ],
    ],
    [
        'key'    => 'mobile_app.general.contact_us',
        'name'   => 'mobile_app::app.settings.general.contact-us.title',
        'info'   => 'mobile_app::app.settings.general.contact-us.info',
        'sort'   => 3,
        'fields' => [
            [
                'name'    => 'contact_telegram',
                'title'   => 'mobile_app::app.settings.fields.contact-telegram',
                'info'    => 'mobile_app::app.settings.fields.contact-telegram-info',
                'type'    => 'text',
                'default' => '',
            ],
            [
                'name'    => 'contact_whatsapp',
                'title'   => 'mobile_app::app.settings.fields.contact-whatsapp',
                'info'    => 'mobile_app::app.settings.fields.contact-whatsapp-info',
                'type'    => 'text',
                'default' => '',
            ],
            [
                'name'    => 'contact_email',
                'title'   => 'mobile_app::app.settings.fields.contact-email',
                'info'    => 'mobile_app::app.settings.fields.contact-email-info',
                'type'    => 'text',
                'default' => '',
            ],
            [
                'name'    => 'contact_max',
                'title'   => 'mobile_app::app.settings.fields.contact-max',
                'info'    => 'mobile_app::app.settings.fields.contact-max-info',
                'type'    => 'text',
                'default' => '',
            ],
        ],
    ],
];


