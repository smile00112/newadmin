<?php

namespace Webkul\Checkout\Repositories;

use Webkul\Core\Eloquent\Repository;

class CartRepository extends Repository
{
    /**
     * Eager load relations for cart (performance: avoid N+1 in collectTotals).
     *
     * @var array
     */
    protected static array $cartRelations = [
        'items.product',
        'customer.addresses',
        'shipping_address',
        'billing_address',
        'shipping_rates',
    ];

    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return 'Webkul\Checkout\Contracts\Cart';
    }

    /**
     * Find cart by id with relations eager loaded.
     *
     * @param  int  $id
     * @param  array  $columns
     * @return mixed
     */
    public function findWithRelations($id, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->with(static::$cartRelations)->find($id, $columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Find one cart by conditions with relations eager loaded.
     *
     * @param  array  $where
     * @param  array  $columns
     * @return mixed
     */
    public function findOneWhereWithRelations(array $where, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->with(static::$cartRelations)->where($where)->first($columns);
        $this->resetModel();

        return $this->parserResult($model);
    }
}
