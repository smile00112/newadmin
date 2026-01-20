<?php

use Webkul\Attribute\Models\Attribute;

use function Pest\Laravel\getJson;

it('returns attributes list with 200 status code', function () {
    // Act & Assert
    getJson('/api/v1/attributes')
        ->assertOk();
});

it('returns attributes list with correct paginated structure', function () {
    // Act
    $response = getJson('/api/v1/attributes');

    // Assert
    $response->assertOk();
    $this->assertPaginatedResponse($response);
});

it('returns attributes list with correct attribute data structure', function () {
    // Act & Assert
    getJson('/api/v1/attributes')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => $this->getAttributeStructure(),
            ],
        ]);
});

it('returns attributes list respecting limit parameter', function () {
    // Act & Assert
    getJson('/api/v1/attributes?limit=5')
        ->assertOk()
        ->assertJsonCount(5, 'data');
});

it('returns attributes list respecting page parameter', function () {
    // Act & Assert
    getJson('/api/v1/attributes?page=1&limit=5')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 1);
});

it('returns single attribute by id with 200 status code', function () {
    // Arrange
    $attribute = Attribute::first();

    // Act & Assert
    getJson('/api/v1/attributes/' . $attribute->id)
        ->assertOk();
});

it('returns single attribute with correct data structure', function () {
    // Arrange
    $attribute = Attribute::first();

    // Act & Assert
    getJson('/api/v1/attributes/' . $attribute->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => $this->getAttributeStructure(),
        ])
        ->assertJsonPath('data.id', $attribute->id);
});

it('returns 404 for non-existent attribute', function () {
    // Act & Assert
    getJson('/api/v1/attributes/999999')
        ->assertNotFound();
});

it('can filter attributes by sort parameter', function () {
    // Act & Assert
    getJson('/api/v1/attributes?sort=id&order=desc')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id'],
            ],
        ]);
});

it('can filter attributes by id parameter', function () {
    // Arrange
    $attribute = Attribute::first();

    // Act & Assert
    getJson('/api/v1/attributes?id=' . $attribute->id)
        ->assertOk()
        ->assertJsonPath('data.0.id', $attribute->id);
});

it('returns attributes without pagination when pagination=0', function () {
    // Act & Assert
    getJson('/api/v1/attributes?pagination=0')
        ->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});
