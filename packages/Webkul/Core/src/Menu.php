<?php

namespace Webkul\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Webkul\Core\Menu\MenuItem;

class Menu
{
    /**
     * Menu items.
     */
    private array $items = [];

    /**
     * Config menu.
     */
    private array $configMenu = [];

    /**
     * Contains current item key.
     */
    private string $currentKey = '';

    /**
     * Menu area for admin.
     */
    const ADMIN = 'admin';

    /**
     * Menu area for customer.
     */
    const CUSTOMER = 'customer';

    /**
     * Add a new menu item.
     */
    public function addItem(MenuItem $menuItem): void
    {
        $this->items[] = $menuItem;
    }

    /**
     * Get all menu items.
     */
    public function getItems(?string $area = null): Collection
    {
        if (! $area) {
            throw new \Exception('Area must be provided to get menu items.');
        }

        $configMenu = collect(config("menu.$area"));

        switch ($area) {
            case self::ADMIN:
                $this->configMenu = $configMenu
                    ->filter(function ($item) {
                        // Для пункта "administration" и всех его дочерних элементов проверяем роль
                        if ($item['key'] === 'administration' || str_starts_with($item['key'], 'administration.')) {
                            $admin = auth()->guard('admin')->user();
                            if (!$admin || !$admin->role) {
                                return false;
                            }

                            echo $admin->role->name. ' - ';
                            // Показываем только для роли "Admin", не для owner
                            // Проверяем, что роль загружена и имеет свойство name
                            if (!isset($admin->role->name) || $admin->role->name !== 'Admin') {
                                return false;
                            }
                            return bouncer()->hasPermission($item['key']);
                        }
                        // Для пункта "settings.users" проверяем роль
                        if ($item['key'] === 'settings.users' || str_starts_with($item['key'], 'settings.users.')) {
                            $admin = auth()->guard('admin')->user();
                            if (!$admin || !$admin->role) {
                                return false;
                            }
                            // Показываем только для роли "Admin"
                            if (!isset($admin->role->name) || $admin->role->name !== 'Admin') {
                                return false;
                            }
                            return bouncer()->hasPermission($item['key']);
                        }
                        return bouncer()->hasPermission($item['key']);
                    })
                    ->toArray();
                break;

            case self::CUSTOMER:
                $canShowWishlist = ! (bool) core()->getConfigData('customer.settings.wishlist.wishlist_option');

                $canShowGdpr = ! (bool) core()->getConfigData('general.gdpr.settings.enabled');

                $this->configMenu = $configMenu
                    ->reject(fn ($item) => ($item['key'] == 'account.wishlist' && $canShowWishlist) || ($item['key'] == 'account.gdpr_data_request' && $canShowGdpr))
                    ->toArray();
                break;

            default:
                $this->configMenu = $configMenu->toArray();

                break;
        }

        if (! $this->items) {
            $this->prepareMenuItems();
        }

        return collect($this->removeUnauthorizedMenuItem())
            ->sortBy('sort');
    }

    /**
     * Prepare menu items.
     */
    private function prepareMenuItems(): void
    {
        $menuWithDotNotation = [];

        foreach ($this->configMenu as $item) {
            try {
                if (isset($item['route']) && strpos(request()->url(), route($item['route'])) !== false) {
                    $this->currentKey = $item['key'];
                }
            } catch (\Exception $e) {
                // Игнорируем ошибки маршрутов при определении текущего пункта меню
            }

            $menuWithDotNotation[$item['key']] = $item;
        }

        $menu = Arr::undot(Arr::dot($menuWithDotNotation));

        foreach ($menu as $menuItemKey => $menuItem) {
            // Пропускаем элементы без обязательных полей
            if (!isset($menuItem['name']) || !isset($menuItem['route']) || !isset($menuItem['sort'])) {
                continue;
            }

            $subMenuItems = $this->processSubMenuItems($menuItem);

            $this->addItem(new MenuItem(
                key: $menuItemKey,
                name: trans($menuItem['name']),
                route: $menuItem['route'],
                sort: $menuItem['sort'],
                icon: $menuItem['icon'] ?? '',
                children: $subMenuItems,
            ));
        }
    }

    /**
     * Process sub menu items.
     */
    private function processSubMenuItems($menuItem): Collection
    {
        return collect($menuItem)
            ->sortBy('sort')
            ->filter(function ($value, $key) {
                // Проверяем, что это массив и имеет необходимые поля для подпункта меню
                // Исключаем служебные поля (key, name, route, sort, icon)
                if (!is_array($value)) {
                    return false;
                }

                // Проверяем наличие обязательных полей
                return isset($value['key'])
                    && isset($value['name'])
                    && isset($value['route'])
                    && isset($value['sort']);
            })
            ->map(function ($subMenuItem) {
                $subSubMenuItems = $this->processSubMenuItems($subMenuItem);

                return new MenuItem(
                    key: $subMenuItem['key'],
                    name: trans($subMenuItem['name']),
                    route: $subMenuItem['route'],
                    sort: $subMenuItem['sort'],
                    icon: $subMenuItem['icon'] ?? '',
                    children: $subSubMenuItems,
                );
            });
    }

    /**
     * Get current active menu.
     */
    public function getCurrentActiveMenu(?string $area = null): ?MenuItem
    {
        $currentKey = implode('.', array_slice(explode('.', $this->currentKey), 0, 2));

        return $this->findMatchingItem($this->getItems($area), $currentKey);
    }

    /**
     * Finding the matching item.
     */
    private function findMatchingItem($items, $currentKey): ?MenuItem
    {
        foreach ($items as $item) {
            if ($item->key == $currentKey) {
                return $item;
            }

            if ($item->haveChildren()) {
                $matchingChild = $this->findMatchingItem($item->getChildren(), $currentKey);

                if ($matchingChild) {
                    return $matchingChild;
                }
            }
        }

        return null;
    }

    /**
     * Remove unauthorized menu item.
     */
    private function removeUnauthorizedMenuItem(): array
    {
        return collect($this->items)->map(function ($item) {
            $this->removeChildrenUnauthorizedMenuItem($item);

            return $item;
        })->toArray();
    }

    /**
     * Remove unauthorized menuItem's children. This will handle all levels.
     */
    private function removeChildrenUnauthorizedMenuItem(MenuItem &$menuItem): void
    {
        if ($menuItem->haveChildren()) {
            $firstChildrenItem = $menuItem->getChildren()->first();

            $menuItem->route = $firstChildrenItem->getRoute();

            $this->removeChildrenUnauthorizedMenuItem($firstChildrenItem);
        }
    }
}
