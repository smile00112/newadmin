<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Index for product_images subquery (base_image, images_count)
        Schema::table('product_images', function (Blueprint $table) {
            if (! $this->hasIndex('product_images', 'product_images_product_id_index')) {
                $table->index('product_id', 'product_images_product_id_index');
            }
        });

        // Index for product_inventories subquery (quantity)
        Schema::table('product_inventories', function (Blueprint $table) {
            if (! $this->hasIndex('product_inventories', 'product_inventories_product_id_index')) {
                $table->index('product_id', 'product_inventories_product_id_index');
            }
        });

        // Index for product_categories subquery (category_id, category_name)
        Schema::table('product_categories', function (Blueprint $table) {
            if (! $this->hasIndex('product_categories', 'product_categories_product_id_index')) {
                $table->index('product_id', 'product_categories_product_id_index');
            }
        });

        // Index for constructor group products subquery (ingredients sum)
        Schema::table('product_constructor_group_products', function (Blueprint $table) {
            if (! $this->hasIndex('product_constructor_group_products', 'pcgp_parent_id_index')) {
                $table->index('parent_id', 'pcgp_parent_id_index');
            }
        });

        // Composite index for product_attribute_values subquery (manage_stock)
        Schema::table('product_attribute_values', function (Blueprint $table) {
            if (! $this->hasIndex('product_attribute_values', 'pav_product_channel_attribute_index')) {
                $table->index(['product_id', 'attribute_id', 'channel'], 'pav_product_channel_attribute_index');
            }
        });

        // Index for product_flat locale filter + grouping
        Schema::table('product_flat', function (Blueprint $table) {
            if (! $this->hasIndex('product_flat', 'product_flat_locale_product_id_index')) {
                $table->index(['locale', 'product_id'], 'product_flat_locale_product_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('product_images_product_id_index');
        });

        Schema::table('product_inventories', function (Blueprint $table) {
            $table->dropIndex('product_inventories_product_id_index');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropIndex('product_categories_product_id_index');
        });

        Schema::table('product_constructor_group_products', function (Blueprint $table) {
            $table->dropIndex('pcgp_parent_id_index');
        });

        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->dropIndex('pav_product_channel_attribute_index');
        });

        Schema::table('product_flat', function (Blueprint $table) {
            $table->dropIndex('product_flat_locale_product_id_index');
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) {
                return true;
            }
        }

        return false;
    }
};
