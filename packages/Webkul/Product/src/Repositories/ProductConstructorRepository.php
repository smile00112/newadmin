<?php

namespace Webkul\Product\Repositories;

use Illuminate\Support\Facades\DB;
use Webkul\Core\Eloquent\Repository;
use Webkul\Product\Models\ProductConstructor;
use Webkul\Product\Models\ProductConstructorGroup;

class ProductConstructorRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductConstructor::class;
    }

    /**
     * Save constructor data.
     *
     * @param  array  $data
     * @param  \Webkul\Product\Models\Product  $product
     * @return void
     */
    public function saveConstructor($data, $product)
    {
        if (! isset($data['constructor']) || ! is_array($data['constructor'])) {
            return;
        }

        $constructors = $data['constructor'];
        $constructorIds = [];

        foreach ($constructors as $constructorData) {
            // Skip empty constructor data
            if (empty($constructorData) || (!isset($constructorData['visible']) && !isset($constructorData['required']))) {
                continue;
            }

            if (isset($constructorData['id']) && $constructorData['id']) {
                $constructor = $this->find($constructorData['id']);
                $constructor->update($constructorData);
            } else {
                $constructorData['parent_id'] = $product->id;
                $constructor = $this->create($constructorData);
            }

            $constructorIds[] = $constructor->id;
            $this->saveConstructorGroups($constructorData, $constructor);
        }

        // Remove constructors that are not in the current data
        $product->constructor()->whereNotIn('id', $constructorIds)->delete();
    }

    /**
     * Save constructor groups.
     *
     * @param  array  $constructorData
     * @param  \Webkul\Product\Models\ProductConstructor  $constructor
     * @return void
     */
    protected function saveConstructorGroups($constructorData, $constructor)
    {
        if (! isset($constructorData['groups'])) {
            return;
        }

        // Handle JSON string format
        if (is_string($constructorData['groups'])) {
            $groups = json_decode($constructorData['groups'], true);
        } else {
            $groups = $constructorData['groups'];
        }

        if (! is_array($groups) || empty($groups)) {
            return;
        }

        $groupIds = [];

        foreach ($groups as $groupData) {
            // Skip empty group data
            if (empty($groupData) || (!isset($groupData['name']) && !isset($groupData['field_type']))) {
                continue;
            }

            if (isset($groupData['id']) && $groupData['id']) {
                $group = ProductConstructorGroup::find($groupData['id']);
                $group->update($groupData);
            } else {
                $groupData['parent_id'] = $constructor->id;
                $group = ProductConstructorGroup::create($groupData);
            }

            $groupIds[] = $group->id;

            $this->saveGroupProducts($groupData, $group);
        }

        // Remove groups that are not in the current data
        $constructor->groups()->whereNotIn('id', $groupIds)->delete();
    }

    /**
     * Save group products.
     *
     * @param  array  $groupData
     * @param  \Webkul\Product\Models\ProductConstructorGroup  $group
     * @return void
     */
    protected function saveGroupProducts($groupData, $group)
    {
        if (! isset($groupData['products'])) {
            return;
        }

        $products = [];
        
        // Get the parent product ID from the constructor
        $parentProductId = $group->constructor->product->id ?? null;
        
        // Handle both array format (from frontend) and key-value format
        if (is_array($groupData['products'])) {
            foreach ($groupData['products'] as $index => $product) {
                // If it's an array with numeric keys, it's an array of products
                if (is_array($product) && isset($product['id'])) {
                    $productId = $product['id'];
                    // Skip if product ID is 0 or invalid
                    if ($productId > 0) {
                        $products[$productId] = [
                            'sort' => $product['sort'] ?? 0,
                            'default' => $product['default'] ?? false,
                            'parent_id' => $parentProductId
                        ];
                    }
                }
                // If it's a key-value format where key is product ID
                elseif (is_numeric($product) && $product > 0) {
                    $productId = $product;
                    $products[$productId] = [
                        'sort' => 0,
                        'default' => false,
                        'parent_id' => $parentProductId
                    ];
                }
            }
        }

        // Only sync if we have valid products
        if (!empty($products)) {
            $group->products()->sync($products);
        } else {
            // If no valid products, detach all existing products
            $group->products()->detach();
        }
    }
}
