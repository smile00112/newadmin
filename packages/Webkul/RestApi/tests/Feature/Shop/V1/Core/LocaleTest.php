<?php

use Webkul\Core\Models\Locale;

use function Pest\Laravel\getJson;

it('returns locales list with 200 status code', function () {
    // Act & Assert
    getJson('/api/v1/locales')
        ->assertOk();
});

it('returns locales list with correct paginated structure', function () {
    // Act
    $response = getJson('/api/v1/locales');

    // Assert
    $response->assertOk();
    $this->assertPaginatedResponse($response);
});

it('returns locales list with correct locale data structure', function () {
    // Act & Assert
    getJson('/api/v1/locales')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => $this->getLocaleStructure(),
            ],
        ]);
});

it('returns locales list respecting limit parameter', function () {
    // Act & Assert
    getJson('/api/v1/locales?limit=1')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('returns locales list respecting page parameter', function () {
    // Act & Assert
    getJson('/api/v1/locales?page=1&limit=1')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 1);
});

it('returns single locale by id with 200 status code', function () {
    // Arrange
    $locale = Locale::first();

    // Act & Assert
    getJson('/api/v1/locales/' . $locale->id)
        ->assertOk();
});

it('returns single locale with correct data structure', function () {
    // Arrange
    $locale = Locale::first();

    // Act & Assert
    getJson('/api/v1/locales/' . $locale->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => $this->getLocaleStructure(),
        ])
        ->assertJsonPath('data.id', $locale->id);
});

it('returns 404 for non-existent locale', function () {
    // Act & Assert
    getJson('/api/v1/locales/999999')
        ->assertNotFound();
});

it('can filter locales by sort parameter', function () {
    // Act & Assert
    getJson('/api/v1/locales?sort=id&order=desc')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id'],
            ],
        ]);
});

it('can filter locales by id parameter', function () {
    // Arrange
    $locale = Locale::first();

    // Act & Assert
    getJson('/api/v1/locales?id=' . $locale->id)
        ->assertOk()
        ->assertJsonPath('data.0.id', $locale->id);
});

it('returns locales without pagination when pagination=0', function () {
    // Act & Assert
    getJson('/api/v1/locales?pagination=0')
        ->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});
