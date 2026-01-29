<?php

use Webkul\Core\Models\Country;

use function Pest\Laravel\getJson;

it('returns countries list with 200 status code', function () {
    // Act & Assert
    getJson('/api/v1/countries')
        ->assertOk();
});

it('returns countries list with correct paginated structure', function () {
    // Act
    $response = getJson('/api/v1/countries');

    // Assert
    $response->assertOk();
    $this->assertPaginatedResponse($response);
});

it('returns countries list with correct country data structure', function () {
    // Act & Assert
    getJson('/api/v1/countries')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => $this->getCountryStructure(),
            ],
        ]);
});

it('returns countries list respecting limit parameter', function () {
    // Act & Assert
    getJson('/api/v1/countries?limit=5')
        ->assertOk()
        ->assertJsonCount(5, 'data');
});

it('returns countries list respecting page parameter', function () {
    // Act & Assert
    getJson('/api/v1/countries?page=1&limit=5')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 1);
});

it('returns single country by id with 200 status code', function () {
    // Arrange
    $country = Country::first();

    // Act & Assert
    getJson('/api/v1/countries/' . $country->id)
        ->assertOk();
});

it('returns single country with correct data structure', function () {
    // Arrange
    $country = Country::first();

    // Act & Assert
    getJson('/api/v1/countries/' . $country->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => $this->getCountryStructure(),
        ])
        ->assertJsonPath('data.id', $country->id);
});

it('returns 404 for non-existent country', function () {
    // Act & Assert
    getJson('/api/v1/countries/999999')
        ->assertNotFound();
});

it('can filter countries by sort parameter', function () {
    // Act & Assert
    getJson('/api/v1/countries?sort=id&order=desc')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id'],
            ],
        ]);
});

it('can filter countries by id parameter', function () {
    // Arrange
    $country = Country::first();

    // Act & Assert
    getJson('/api/v1/countries?id=' . $country->id)
        ->assertOk()
        ->assertJsonPath('data.0.id', $country->id);
});

it('returns countries without pagination when pagination=0', function () {
    // Act & Assert
    getJson('/api/v1/countries?pagination=0')
        ->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});

it('returns countries states list with 200 status code', function () {
    // Act & Assert
    getJson('/api/v1/countries-states')
        ->assertOk();
});

it('returns countries states with correct structure', function () {
    // Act & Assert
    getJson('/api/v1/countries-states')
        ->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});

it('returns countries states groups with 200 status code', function () {
    // Act & Assert
    getJson('/api/v1/countries/states/groups')
        ->assertOk();
});

it('returns countries states groups with correct structure', function () {
    // Act & Assert
    getJson('/api/v1/countries/states/groups')
        ->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});
