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
        if (! isset($data['constructor'])) {
            return;
        }

        $constructorData = $data['constructor'];

        if (isset($constructorData['id']) && $constructorData['id']) {
            $constructor = $this->find($constructorData['id']);
            $constructor->update($constructorData);
        } else {
            $constructorData['parent_id'] = $product->id;
            $constructor = $this->create($constructorData);
        }

        $this->saveConstructorGroups($constructorData, $constructor);
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

        $groupIds = [];

        foreach ($constructorData['groups'] as $groupData) {
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

        $productIds = array_filter($groupData['products']);
        $group->products()->sync($productIds);
    }
}
