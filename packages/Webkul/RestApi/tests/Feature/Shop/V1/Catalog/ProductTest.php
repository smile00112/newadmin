<?php

use Webkul\Faker\Helpers\Product as ProductFaker;

use function Pest\Laravel\getJson;

it('returns products list with 200 status code', function () {
    // Arrange
    (new ProductFaker)->getSimpleProductFactory()->create();

    // Act & Assert
    getJson('/api/v1/products')
        ->assertOk();
});

it('returns products list with correct paginated structure', function () {
    // Arrange
    (new ProductFaker)->getSimpleProductFactory()->create();

    // Act
    $response = getJson('/api/v1/products');

    // Assert
    $response->assertOk();
    $this->assertPaginatedResponse($response);
});

it('returns products list with correct product data structure', function () {
    // Arrange
    (new ProductFaker)->getSimpleProductFactory()->create();

    // Act & Assert
    getJson('/api/v1/products')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => $this->getProductStructure(),
            ],
        ]);
});

it('returns products list respecting limit parameter', function () {
    // Arrange
    (new ProductFaker)->getSimpleProductFactory()->count(5)->create();

    // Act & Assert
    getJson('/api/v1/products?limit=2')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('returns products list respecting page parameter', function () {
    // Arrange
    (new ProductFaker)->getSimpleProductFactory()->count(5)->create();

    // Act & Assert
    getJson('/api/v1/products?page=1&limit=2')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonCount(2, 'data');
});

it('returns single product by id with 200 status code', function () {
    // Arrange
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act & Assert
    getJson('/api/v1/products/' . $product->id)
        ->assertOk();
});

it('returns single product with correct data structure', function () {
    // Arrange
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act & Assert
    getJson('/api/v1/products/' . $product->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => $this->getProductStructure(),
        ])
        ->assertJsonPath('data.id', $product->id);
});

it('returns 404 for non-existent product', function () {
    // Act & Assert
    getJson('/api/v1/products/999999')
        ->assertNotFound();
});

it('returns product additional information with 200 status code', function () {
    // Arrange
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act & Assert
    getJson('/api/v1/products/' . $product->id . '/additional-information')
        ->assertOk();
});

it('returns product additional information with correct structure', function () {
    // Arrange
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act & Assert
    getJson('/api/v1/products/' . $product->id . '/additional-information')
        ->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});

it('returns 404 for additional information of non-existent product', function () {
    // Act & Assert
    getJson('/api/v1/products/999999/additional-information')
        ->assertNotFound();
});

it('returns product configurable config with 200 status code', function () {
    // Arrange
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act & Assert
    getJson('/api/v1/products/' . $product->id . '/configurable-config')
        ->assertOk();
});

it('returns product configurable config with correct structure', function () {
    // Arrange
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act & Assert
    getJson('/api/v1/products/' . $product->id . '/configurable-config')
        ->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});

it('returns 404 for configurable config of non-existent product', function () {
    // Act & Assert
    getJson('/api/v1/products/999999/configurable-config')
        ->assertNotFound();
});

it('returns product reviews with 200 status code', function () {
    // Arrange
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act & Assert
    getJson('/api/v1/products/' . $product->id . '/reviews')
        ->assertOk();
});

it('returns product reviews with correct paginated structure', function () {
    // Arrange
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act
    $response = getJson('/api/v1/products/' . $product->id . '/reviews');

    // Assert
    $response->assertOk();
    $this->assertPaginatedResponse($response);
});

it('returns 404 for reviews of non-existent product', function () {
    // Act & Assert
    getJson('/api/v1/products/999999/reviews')
        ->assertNotFound();
});

it('can filter products by sort parameter', function () {
    // Arrange
    (new ProductFaker)->getSimpleProductFactory()->count(3)->create();

    // Act & Assert
    getJson('/api/v1/products?sort=id&order=desc')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id'],
            ],
        ]);
});

it('can filter products by id parameter', function () {
    // Arrange
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act & Assert
    getJson('/api/v1/products?id=' . $product->id)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $product->id);
});
