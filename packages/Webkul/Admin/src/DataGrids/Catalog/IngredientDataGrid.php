<?php

namespace Webkul\Admin\DataGrids\Catalog;

class IngredientDataGrid extends ProductDataGrid
{
    /**
     * Always render ingredient-only grid for dedicated route.
     */
    protected function shouldShowIngredientsOnly(): bool
    {
        return true;
    }
}
