<?php

return [
    [
        'key'   => 'iiko',
        'name'  => 'iiko-integration::app.acl.title',
        'route' => 'admin.iiko.management.index',
        'sort'  => 10,
    ],
    [
        'key'   => 'iiko.management',
        'name'  => 'iiko-integration::app.acl.management',
        'route' => 'admin.iiko.management.index',
        'sort'  => 1,
    ],
    [
        'key'   => 'iiko.settings',
        'name'  => 'iiko-integration::app.acl.settings',
        'route' => 'admin.iiko.settings.index',
        'sort'  => 2,
    ],
];
