<?php

use Webkul\Category\Models\Category;
use Webkul\Faker\Helpers\Category as CategoryFaker;
use Webkul\Faker\Helpers\Product as ProductFaker;

use function Pest\Laravel\getJson;

it('returns catalog list with 200 status code', function () {
    // Act & Assert
    getJson('/api/v1/catalog')
        ->assertOk();
});

it('returns catalog list with correct paginated structure', function () {
    // Act
    $response = getJson('/api/v1/catalog');

    // Assert
    $response->assertOk();
    $this->assertPaginatedResponse($response);
});

it('returns catalog list with correct category data structure including products', function () {
    // Arrange
    $category = (new CategoryFaker)->factory()->create();

    (new ProductFaker)
        ->getSimpleProductFactory()
        ->hasAttached($category)
        ->create();

    // Act & Assert
    getJson('/api/v1/catalog')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => $this->getCatalogCategoryStructure(),
            ],
        ]);
});

it('returns catalog list respecting limit parameter', function () {
    // Arrange
    (new CategoryFaker)->factory()->count(5)->create();

    // Act & Assert
    getJson('/api/v1/catalog?limit=2')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('returns catalog list respecting page parameter', function () {
    // Arrange
    (new CategoryFaker)->factory()->count(5)->create();

    // Act & Assert
    getJson('/api/v1/catalog?page=1&limit=2')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonCount(2, 'data');
});

it('returns single catalog category by id with 200 status code', function () {
    // Arrange
    $category = (new CategoryFaker)->factory()->create();

    // Act & Assert
    getJson('/api/v1/catalog/' . $category->id)
        ->assertOk();
});

it('returns single catalog category with correct data structure including products', function () {
    // Arrange
    $category = (new CategoryFaker)->factory()->create();

    (new ProductFaker)
        ->getSimpleProductFactory()
        ->hasAttached($category)
        ->create();

    // Act & Assert
    getJson('/api/v1/catalog/' . $category->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => $this->getCatalogCategoryStructure(),
        ])
        ->assertJsonPath('data.id', $category->id);
});

it('returns 404 for non-existent catalog category', function () {
    // Act & Assert
    getJson('/api/v1/catalog/999999')
        ->assertNotFound();
});

it('returns catalog category with products array', function () {
    // Arrange
    $category = (new CategoryFaker)->factory()->create();

    $product = (new ProductFaker)
        ->getSimpleProductFactory()
        ->hasAttached($category)
        ->create();

    // Act & Assert
    getJson('/api/v1/catalog/' . $category->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'products' => [
                    '*' => $this->getProductStructure(),
                ],
            ],
        ]);
});

it('can filter catalog by sort parameter', function () {
    // Arrange
    (new CategoryFaker)->factory()->count(3)->create();

    // Act & Assert
    getJson('/api/v1/catalog?sort=id&order=desc')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id'],
            ],
        ]);
});

it('can filter catalog by id parameter', function () {
    // Arrange
    $category = (new CategoryFaker)->factory()->create();

    // Act & Assert
    getJson('/api/v1/catalog?id=' . $category->id)
        ->assertOk()
        ->assertJsonPath('data.0.id', $category->id);
});

it('returns catalog without pagination when pagination=0', function () {
    // Act & Assert
    getJson('/api/v1/catalog?pagination=0')
        ->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});
