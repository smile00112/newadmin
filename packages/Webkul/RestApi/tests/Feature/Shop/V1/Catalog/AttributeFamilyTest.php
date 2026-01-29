<?php

use Webkul\Attribute\Models\AttributeFamily;

use function Pest\Laravel\getJson;

it('returns attribute families list with 200 status code', function () {
    // Act & Assert
    getJson('/api/v1/attribute-families')
        ->assertOk();
});

it('returns attribute families list with correct paginated structure', function () {
    // Act
    $response = getJson('/api/v1/attribute-families');

    // Assert
    $response->assertOk();
    $this->assertPaginatedResponse($response);
});

it('returns attribute families list with correct data structure', function () {
    // Act & Assert
    getJson('/api/v1/attribute-families')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => $this->getAttributeFamilyStructure(),
            ],
        ]);
});

it('returns attribute families list respecting limit parameter', function () {
    // Act & Assert
    getJson('/api/v1/attribute-families?limit=1')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('returns attribute families list respecting page parameter', function () {
    // Act & Assert
    getJson('/api/v1/attribute-families?page=1&limit=1')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 1);
});

it('returns single attribute family by id with 200 status code', function () {
    // Arrange
    $attributeFamily = AttributeFamily::first();

    // Act & Assert
    getJson('/api/v1/attribute-families/' . $attributeFamily->id)
        ->assertOk();
});

it('returns single attribute family with correct data structure', function () {
    // Arrange
    $attributeFamily = AttributeFamily::first();

    // Act & Assert
    getJson('/api/v1/attribute-families/' . $attributeFamily->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => $this->getAttributeFamilyStructure(),
        ])
        ->assertJsonPath('data.id', $attributeFamily->id);
});

it('returns 404 for non-existent attribute family', function () {
    // Act & Assert
    getJson('/api/v1/attribute-families/999999')
        ->assertNotFound();
});

it('can filter attribute families by sort parameter', function () {
    // Act & Assert
    getJson('/api/v1/attribute-families?sort=id&order=desc')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id'],
            ],
        ]);
});

it('can filter attribute families by id parameter', function () {
    // Arrange
    $attributeFamily = AttributeFamily::first();

    // Act & Assert
    getJson('/api/v1/attribute-families?id=' . $attributeFamily->id)
        ->assertOk()
        ->assertJsonPath('data.0.id', $attributeFamily->id);
});

it('returns attribute families without pagination when pagination=0', function () {
    // Act & Assert
    getJson('/api/v1/attribute-families?pagination=0')
        ->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});

it('returns attribute family with groups data', function () {
    // Arrange
    $attributeFamily = AttributeFamily::first();

    // Act & Assert
    getJson('/api/v1/attribute-families/' . $attributeFamily->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'groups' => [
                    '*' => [
                        'id',
                        'name',
                        'position',
                        'custom_attributes',
                    ],
                ],
            ],
        ]);
});
