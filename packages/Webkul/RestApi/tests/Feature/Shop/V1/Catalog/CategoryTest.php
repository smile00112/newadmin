<?php

use Webkul\Category\Models\Category;
use Webkul\Faker\Helpers\Category as CategoryFaker;
use Webkul\Faker\Helpers\Product as ProductFaker;

use function Pest\Laravel\getJson;

it('returns categories list with 200 status code', function () {
    // Act & Assert
    getJson('/api/v1/categories')
        ->assertOk();
});

it('returns categories list with correct paginated structure', function () {
    // Act
    $response = getJson('/api/v1/categories');

    // Assert
    $response->assertOk();
    $this->assertPaginatedResponse($response);
});

it('returns categories list with correct category data structure', function () {
    // Arrange
    (new CategoryFaker)->factory()->create();

    // Act & Assert
    getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => $this->getCategoryStructure(),
            ],
        ]);
});

it('returns categories list respecting limit parameter', function () {
    // Arrange
    (new CategoryFaker)->factory()->count(5)->create();

    // Act & Assert
    getJson('/api/v1/categories?limit=2')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('returns categories list respecting page parameter', function () {
    // Arrange
    (new CategoryFaker)->factory()->count(5)->create();

    // Act & Assert
    getJson('/api/v1/categories?page=1&limit=2')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonCount(2, 'data');
});

it('returns single category by id with 200 status code', function () {
    // Arrange
    $category = (new CategoryFaker)->factory()->create();

    // Act & Assert
    getJson('/api/v1/categories/' . $category->id)
        ->assertOk();
});

it('returns single category with correct data structure', function () {
    // Arrange
    $category = (new CategoryFaker)->factory()->create();

    // Act & Assert
    getJson('/api/v1/categories/' . $category->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => $this->getCategoryStructure(),
        ])
        ->assertJsonPath('data.id', $category->id);
});

it('returns 404 for non-existent category', function () {
    // Act & Assert
    getJson('/api/v1/categories/999999')
        ->assertNotFound();
});

it('returns category max price with 200 status code', function () {
    // Arrange
    $category = (new CategoryFaker)->factory()->create();

    // Act & Assert
    getJson('/api/v1/categories/max-price/' . $category->id)
        ->assertOk();
});

it('returns category max price with correct structure', function () {
    // Arrange
    $category = (new CategoryFaker)->factory()->create();

    (new ProductFaker)
        ->getSimpleProductFactory()
        ->hasAttached($category)
        ->create();

    // Act & Assert
    getJson('/api/v1/categories/max-price/' . $category->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'max_price',
            ],
        ]);
});

it('returns 404 for max price of non-existent category', function () {
    // Act & Assert
    getJson('/api/v1/categories/max-price/999999')
        ->assertNotFound();
});

it('returns descendant categories with 200 status code', function () {
    // Act & Assert
    getJson('/api/v1/descendant-categories')
        ->assertOk();
});

it('returns descendant categories with correct structure', function () {
    // Arrange
    $parentCategory = Category::first() ?? (new CategoryFaker)->factory()->create();

    (new CategoryFaker)->factory()->create([
        'parent_id' => $parentCategory->id,
    ]);

    // Act & Assert
    getJson('/api/v1/descendant-categories')
        ->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});

it('can filter categories by sort parameter', function () {
    // Arrange
    (new CategoryFaker)->factory()->count(3)->create();

    // Act & Assert
    getJson('/api/v1/categories?sort=id&order=desc')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id'],
            ],
        ]);
});

it('can filter categories by id parameter', function () {
    // Arrange
    $category = (new CategoryFaker)->factory()->create();

    // Act & Assert
    getJson('/api/v1/categories?id=' . $category->id)
        ->assertOk()
        ->assertJsonPath('data.0.id', $category->id);
});
