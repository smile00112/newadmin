<?php

namespace Webkul\MobileApp\Config;

use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\CMS\Repositories\PageRepository;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shipping\Facades\Shipping;

class FieldsConfig
{
    /**
     * Get all available field definitions for mobile app settings.
     * Each field can have a 'source' for dynamic options loading.
     */
    public function getFields(): array
    {
        return [
            [
                'key'         => 'featured_categories',
                'title'       => 'mobile_app::app.settings.fields.featured-categories',
                'type'        => 'multiselect',
                'source'      => 'categories',
                'description' => 'mobile_app::app.settings.fields.featured-categories-info',
            ],
            [
                'key'         => 'featured_products',
                'title'       => 'mobile_app::app.settings.fields.featured-products',
                'type'        => 'multiselect',
                'source'      => 'products',
                'description' => 'mobile_app::app.settings.fields.featured-products-info',
            ],
            [
                'key'         => 'default_channel',
                'title'       => 'mobile_app::app.settings.fields.default-channel',
                'type'        => 'select',
                'source'      => 'channels',
                'description' => 'mobile_app::app.settings.fields.default-channel-info',
            ],
            [
                'key'         => 'shipping_methods',
                'title'       => 'mobile_app::app.settings.fields.shipping-methods',
                'type'        => 'multiselect',
                'source'      => 'shipping_methods',
                'description' => 'mobile_app::app.settings.fields.shipping-methods-info',
            ],
            [
                'key'         => 'app_name',
                'title'       => 'mobile_app::app.settings.fields.app-name',
                'type'        => 'text',
                'description' => 'mobile_app::app.settings.fields.app-name-info',
            ],
            [
                'key'         => 'app_version',
                'title'       => 'mobile_app::app.settings.fields.app-version',
                'type'        => 'text',
                'description' => 'mobile_app::app.settings.fields.app-version-info',
            ],
            [
                'key'         => 'maintenance_mode',
                'title'       => 'mobile_app::app.settings.fields.maintenance-mode',
                'type'        => 'boolean',
                'default'     => false,
                'description' => 'mobile_app::app.settings.fields.maintenance-mode-info',
            ],
            [
                'key'         => 'force_update',
                'title'       => 'mobile_app::app.settings.fields.force-update',
                'type'        => 'boolean',
                'default'     => false,
                'description' => 'mobile_app::app.settings.fields.force-update-info',
            ],
            [
                'key'         => 'min_app_version',
                'title'       => 'mobile_app::app.settings.fields.min-app-version',
                'type'        => 'text',
                'description' => 'mobile_app::app.settings.fields.min-app-version-info',
            ],
            [
                'key'         => 'home_filters',
                'title'       => 'mobile_app::app.settings.fields.home-filters',
                'type'        => 'multiselect',
                'source'      => 'attributes',
                'description' => 'mobile_app::app.settings.fields.home-filters-info',
            ],
            [
                'key'         => 'custom_data',
                'title'       => 'mobile_app::app.settings.fields.custom-data',
                'type'        => 'textarea',
                'description' => 'mobile_app::app.settings.fields.custom-data-info',
            ],
            [
                'key'         => 'contact_telegram',
                'title'       => 'mobile_app::app.settings.fields.contact-telegram',
                'type'        => 'text',
                'description' => 'mobile_app::app.settings.fields.contact-telegram-info',
                'group'       => 'contact',
            ],
            [
                'key'         => 'contact_whatsapp',
                'title'       => 'mobile_app::app.settings.fields.contact-whatsapp',
                'type'        => 'text',
                'description' => 'mobile_app::app.settings.fields.contact-whatsapp-info',
                'group'       => 'contact',
            ],
            [
                'key'         => 'contact_email',
                'title'       => 'mobile_app::app.settings.fields.contact-email',
                'type'        => 'text',
                'description' => 'mobile_app::app.settings.fields.contact-email-info',
                'group'       => 'contact',
            ],
            [
                'key'         => 'contact_max',
                'title'       => 'mobile_app::app.settings.fields.contact-max',
                'type'        => 'text',
                'description' => 'mobile_app::app.settings.fields.contact-max-info',
                'group'       => 'contact',
            ],
            [
                'key'         => 'user_agreement',
                'title'       => 'mobile_app::app.settings.fields.user-agreement',
                'type'        => 'select',
                'source'      => 'cms_pages',
                'description' => 'mobile_app::app.settings.fields.user-agreement-info',
                'group'       => 'documents',
            ],
            [
                'key'         => 'privacy_policy',
                'title'       => 'mobile_app::app.settings.fields.privacy-policy',
                'type'        => 'select',
                'source'      => 'cms_pages',
                'description' => 'mobile_app::app.settings.fields.privacy-policy-info',
                'group'       => 'documents',
            ],
        ];
    }

    /**
     * Get options for a specific source.
     */
    public function getOptionsForSource(string $source): array
    {
        return match ($source) {
            'categories'       => $this->getCategoryOptions(),
            'products'         => $this->getProductOptions(),
            'channels'         => $this->getChannelOptions(),
            'shipping_methods' => $this->getShippingMethodOptions(),
            'attributes'       => $this->getAttributeOptions(),
            'cms_pages'        => $this->getCmsPageOptions(),
            default            => [],
        };
    }

    /**
     * Get category options.
     */
    protected function getCategoryOptions(): array
    {
        $repository = app(CategoryRepository::class);
        
        return $repository->all()->map(function ($category) {
            return [
                'id'    => $category->id,
                'title' => $category->name,
                'value' => $category->id,
            ];
        })->toArray();
    }

    /**
     * Get product options.
     */
    protected function getProductOptions(): array
    {
        $repository = app(ProductRepository::class);
        
        return $repository->take(100)->get()->map(function ($product) {
            return [
                'id'    => $product->id,
                'title' => $product->name ?? $product->sku,
                'value' => $product->id,
            ];
        })->toArray();
    }

    /**
     * Get channel options.
     */
    protected function getChannelOptions(): array
    {
        $repository = app(ChannelRepository::class);
        
        return $repository->all()->map(function ($channel) {
            return [
                'id'    => $channel->id,
                'title' => $channel->name,
                'value' => $channel->code,
            ];
        })->toArray();
    }

    /**
     * Get shipping method options.
     */
    protected function getShippingMethodOptions(): array
    {
        $methods = [];
        
        foreach (config('carriers', []) as $code => $carrier) {
            $methods[] = [
                'id'    => $code,
                'title' => $carrier['title'] ?? $code,
                'value' => $code,
            ];
        }
        
        return $methods;
    }

    /**
     * Get attribute options.
     */
    protected function getAttributeOptions(): array
    {
        $repository = app(AttributeRepository::class);
        
        return $repository->all()->map(function ($attribute) {
            return [
                'id'    => $attribute->id,
                'title' => $attribute->admin_name ?? $attribute->code,
                'value' => $attribute->code,
            ];
        })->toArray();
    }

    /**
     * Get CMS page options.
     */
    protected function getCmsPageOptions(): array
    {
        $repository = app(PageRepository::class);
        
        return $repository->all()->map(function ($page) {
            return [
                'id'    => $page->id,
                'title' => $page->page_title ?? $page->url_key ?? 'Page #' . $page->id,
                'value' => $page->id,
            ];
        })->toArray();
    }
}

