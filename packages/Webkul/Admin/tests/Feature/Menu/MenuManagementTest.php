<?php

use Illuminate\Support\Facades\DB;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;
use function Pest\Laravel\put;

it('shows menus index page', function () {
    $this->loginAsAdmin();

    get(route('admin.menu.menus.index'))
        ->assertOk()
        ->assertSeeText(trans('menu::app.admin.menus.title'));
});

it('creates and updates menu', function () {
    $this->loginAsAdmin();

    post(route('admin.menu.menus.store'), [
        'name'      => 'Header Main',
        'code'      => 'header_main',
        'location'  => 'header',
        'is_active' => 1,
    ])->assertRedirect(route('admin.menu.menus.index'));

    $menuId = DB::table('site_menus')->where('code', 'header_main')->value('id');

    expect($menuId)->not->toBeNull();

    put(route('admin.menu.menus.update', $menuId), [
        'name'      => 'Header Main Updated',
        'code'      => 'header_main',
        'location'  => 'header',
        'is_active' => 0,
    ])->assertRedirect(route('admin.menu.menus.index'));

    $this->assertDatabaseHas('site_menus', [
        'id'        => $menuId,
        'name'      => 'Header Main Updated',
        'is_active' => 0,
    ]);
});

it('creates cms and custom menu items and sorts them', function () {
    $this->loginAsAdmin();

    DB::table('site_menus')->insert([
        'name'       => 'Footer Menu',
        'code'       => 'footer_menu',
        'location'   => 'footer',
        'is_active'  => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $menuId = DB::table('site_menus')->where('code', 'footer_menu')->value('id');

    DB::table('cms_pages')->insert([
        'layout'     => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $cmsPageId = DB::table('cms_pages')->max('id');

    postJson(route('admin.menu.items.store', $menuId), [
        'title'       => 'About',
        'type'        => 'cms_page',
        'cms_page_id' => $cmsPageId,
        'target'      => '_self',
        'is_active'   => 1,
    ])->assertOk();

    postJson(route('admin.menu.items.store', $menuId), [
        'title'     => 'Contact',
        'type'      => 'custom_url',
        'url'       => '/contact',
        'target'    => '_blank',
        'is_active' => 1,
    ])->assertOk();

    $aboutId = DB::table('site_menu_items')->where('title', 'About')->value('id');
    $contactId = DB::table('site_menu_items')->where('title', 'Contact')->value('id');

    postJson(route('admin.menu.items.sort', $menuId), [
        'tree' => [
            [
                'id'       => $contactId,
                'children' => [
                    [
                        'id'       => $aboutId,
                        'children' => [],
                    ],
                ],
            ],
        ],
    ])->assertOk();

    $this->assertDatabaseHas('site_menu_items', [
        'id'        => $contactId,
        'parent_id' => null,
        'sort_order'=> 0,
    ]);

    $this->assertDatabaseHas('site_menu_items', [
        'id'        => $aboutId,
        'parent_id' => $contactId,
        'sort_order'=> 0,
    ]);

    deleteJson(route('admin.menu.items.delete', [$menuId, $aboutId]))->assertOk();
    deleteJson(route('admin.menu.items.delete', [$menuId, $contactId]))->assertOk();
});
