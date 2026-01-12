<?php

namespace Webkul\Core\Menu;

use Illuminate\Support\Collection;

class MenuItem
{
    /**
     * Create a new MenuItem instance.
     *
     * @return void
     */
    public function __construct(
        public string $key,
        public string $name,
        public string $route,
        public int $sort,
        public string $icon,
        public Collection $children,
    ) {}

    /**
     * Get name of menu item.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the icon of menu item.
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Get current route.
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Get the url of the menu item.
     */
    public function getUrl(): string
    {
        return route($this->getRoute());
    }

    /**
     * Get the key of the menu item.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Check weather menu item have children or not.
     */
    public function haveChildren(): bool
    {
        return $this->children->isNotEmpty();
    }

    /**
     * Get children of menu item.
     */
    public function getChildren(): Collection
    {
        if (! $this->haveChildren()) {
            return collect();
        }

        return $this->children;
    }

    /**
     * Check weather menu item is active or not.
     */
    public function isActive(): bool
    {
        /*TODO refactor*/
        if( !empty($_GET['ingredient']) && strpos($this->getUrl(), 'admin/catalog/ingredients') !== false){
            return true;
        }

        // Более надежная проверка через имя маршрута
        $currentRoute = request()->route()?->getName();
        $menuRoute = $this->getRoute();

        if ($currentRoute === $menuRoute) {
            /*TODO refactor*/
            if(!empty($_GET['ingredient']) && strpos($this->getUrl(), 'admin/catalog/products') !== false){
                return false;
            }
            return true;
        }

        // Fallback: проверка по пути (без домена) - более надежно чем fullUrlIs
        $currentPath = request()->path();
        $menuPath = parse_url($this->getUrl(), PHP_URL_PATH);

        // Убираем начальный слэш для сравнения
        $menuPath = ltrim($menuPath, '/');
        $currentPath = ltrim($currentPath, '/');

        if ($menuPath && str_starts_with($currentPath, $menuPath)) {
            /*TODO refactor*/
            if(!empty($_GET['ingredient']) && strpos($this->getUrl(), 'admin/catalog/products') !== false){
                return false;
            }
            return true;
        }

        if ($this->haveChildren()) {
            foreach ($this->getChildren() as $child) {
                if ($child->isActive()) {
                    return true;
                }
            }
        }

        return false;
    }
}
