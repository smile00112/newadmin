<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bonus
    |--------------------------------------------------------------------------
    |
    | All ACLs related to bonus will be placed here.
    |
    */
    [
        'key'   => 'bonus',
        'name'  => 'bonus::app.admin.acl.bonus',
        'route' => 'admin.configuration.index',
        'sort'  => 10,
    ], [
        'key'   => 'bonus.settings',
        'name'  => 'bonus::app.admin.acl.settings',
        'route' => 'admin.configuration.index',
        'sort'  => 1,
    ], [
        'key'   => 'bonus.levels',
        'name'  => 'bonus::app.admin.acl.levels',
        'route' => 'admin.bonus.levels.index',
        'sort'  => 2,
    ], [
        'key'   => 'bonus.levels.create',
        'name'  => 'bonus::app.admin.acl.create',
        'route' => 'admin.bonus.levels.create',
        'sort'  => 1,
    ], [
        'key'   => 'bonus.levels.edit',
        'name'  => 'bonus::app.admin.acl.edit',
        'route' => 'admin.bonus.levels.edit',
        'sort'  => 2,
    ], [
        'key'   => 'bonus.levels.delete',
        'name'  => 'bonus::app.admin.acl.delete',
        'route' => 'admin.bonus.levels.destroy',
        'sort'  => 3,
    ], [
        'key'   => 'bonus.transactions',
        'name'  => 'bonus::app.admin.acl.transactions',
        'route' => 'admin.bonus.transactions.index',
        'sort'  => 3,
    ],
];
