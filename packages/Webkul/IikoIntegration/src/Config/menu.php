<?php

return [
    /**
     * iiko Integration.
     */
    [
        'key'        => 'iiko',
        'name'       => 'iiko-integration::app.menu.title',
        'route'      => 'admin.iiko.management.index',
        'sort'       => 10,
        'icon'       => 'icon-settings',
    ], [
        'key'        => 'iiko.management',
        'name'       => 'iiko-integration::app.menu.management',
        'route'      => 'admin.iiko.management.index',
        'sort'       => 1,
        'icon'       => '',
    ], [
        'key'        => 'iiko.settings',
        'name'       => 'iiko-integration::app.menu.settings',
        'route'      => 'admin.iiko.settings.index',
        'sort'       => 2,
        'icon'       => '',
    ],
];
