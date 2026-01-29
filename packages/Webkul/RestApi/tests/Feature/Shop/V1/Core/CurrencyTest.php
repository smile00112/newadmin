<?php

use Webkul\Core\Models\Currency;

use function Pest\Laravel\getJson;

it('returns currencies list with 200 status code', function () {
    // Act & Assert
    getJson('/api/v1/currencies')
        ->assertOk();
});

it('returns currencies list with correct paginated structure', function () {
    // Act
    $response = getJson('/api/v1/currencies');

    // Assert
    $response->assertOk();
    $this->assertPaginatedResponse($response);
});

it('returns currencies list with correct currency data structure', function () {
    // Act & Assert
    getJson('/api/v1/currencies')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => $this->getCurrencyStructure(),
            ],
        ]);
});

it('returns currencies list respecting limit parameter', function () {
    // Act & Assert
    getJson('/api/v1/currencies?limit=1')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('returns currencies list respecting page parameter', function () {
    // Act & Assert
    getJson('/api/v1/currencies?page=1&limit=1')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 1);
});

it('returns single currency by id with 200 status code', function () {
    // Arrange
    $currency = Currency::first();

    // Act & Assert
    getJson('/api/v1/currencies/' . $currency->id)
        ->assertOk();
});

it('returns single currency with correct data structure', function () {
    // Arrange
    $currency = Currency::first();

    // Act & Assert
    getJson('/api/v1/currencies/' . $currency->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => $this->getCurrencyStructure(),
        ])
        ->assertJsonPath('data.id', $currency->id);
});

it('returns 404 for non-existent currency', function () {
    // Act & Assert
    getJson('/api/v1/currencies/999999')
        ->assertNotFound();
});

it('can filter currencies by sort parameter', function () {
    // Act & Assert
    getJson('/api/v1/currencies?sort=id&order=desc')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id'],
            ],
        ]);
});

it('can filter currencies by id parameter', function () {
    // Arrange
    $currency = Currency::first();

    // Act & Assert
    getJson('/api/v1/currencies?id=' . $currency->id)
        ->assertOk()
        ->assertJsonPath('data.0.id', $currency->id);
});

it('returns currencies without pagination when pagination=0', function () {
    // Act & Assert
    getJson('/api/v1/currencies?pagination=0')
        ->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});
