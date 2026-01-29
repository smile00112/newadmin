<?php

namespace Webkul\RestApi\Tests\Concerns;

use Illuminate\Testing\TestResponse;

trait ApiAssertions
{
    /**
     * Assert that the response has a valid paginated structure.
     */
    protected function assertPaginatedResponse(TestResponse $response): TestResponse
    {
        return $response->assertJsonStructure([
            'data',
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);
    }

    /**
     * Get the expected Product structure for API responses.
     */
    protected function getProductStructure(): array
    {
        return [
            'id',
            'sku',
            'type',
            'name',
            'url_key',
            'price',
            'formatted_price',
            'short_description',
            'description',
            'images',
            'base_image',
            'in_stock',
            'is_saved',
            'is_item_in_cart',
            'show_quantity_changer',
            'reviews',
        ];
    }

    /**
     * Get the expected Category structure for API responses.
     */
    protected function getCategoryStructure(): array
    {
        return [
            'id',
            'name',
            'slug',
            'display_mode',
            'description',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'status',
            'image_url',
            'category_icon_path',
            'additional',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Get the expected Attribute structure for API responses.
     */
    protected function getAttributeStructure(): array
    {
        return [
            'id',
            'code',
            'admin_name',
            'type',
            'validation',
            'position',
            'is_comparable',
            'is_configurable',
            'is_required',
            'is_unique',
            'is_filterable',
            'is_visible_on_front',
            'use_in_flat',
            'value_per_locale',
            'value_per_channel',
            'swatch_type',
            'options',
        ];
    }

    /**
     * Get the expected AttributeFamily structure for API responses.
     */
    protected function getAttributeFamilyStructure(): array
    {
        return [
            'id',
            'code',
            'name',
            'status',
            'groups',
        ];
    }

    /**
     * Get the expected Locale structure for API responses.
     */
    protected function getLocaleStructure(): array
    {
        return [
            'id',
            'code',
            'name',
            'direction',
            'logo_url',
        ];
    }

    /**
     * Get the expected Currency structure for API responses.
     */
    protected function getCurrencyStructure(): array
    {
        return [
            'id',
            'code',
            'name',
            'symbol',
        ];
    }

    /**
     * Get the expected Channel structure for API responses.
     */
    protected function getChannelStructure(): array
    {
        return [
            'id',
            'code',
            'name',
            'description',
            'timezone',
            'theme',
            'hostname',
            'logo_url',
            'favicon_url',
            'default_locale',
            'root_category_id',
            'locales',
            'currencies',
        ];
    }

    /**
     * Get the expected Country structure for API responses.
     */
    protected function getCountryStructure(): array
    {
        return [
            'id',
            'code',
            'name',
        ];
    }

    /**
     * Get the expected Customer structure for API responses.
     */
    protected function getCustomerStructure(): array
    {
        return [
            'id',
            'first_name',
            'last_name',
            'name',
            'gender',
            'date_of_birth',
            'email',
            'phone',
            'image_url',
            'status',
            'notes',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Get the expected CatalogCategory structure for API responses.
     */
    protected function getCatalogCategoryStructure(): array
    {
        return [
            'id',
            'name',
            'slug',
            'display_mode',
            'description',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'status',
            'image_url',
            'category_icon_path',
            'additional',
            'created_at',
            'updated_at',
            'products',
        ];
    }
}
