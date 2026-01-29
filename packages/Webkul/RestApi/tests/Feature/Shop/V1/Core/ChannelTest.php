<?php

use Webkul\Core\Models\Channel;

use function Pest\Laravel\getJson;

it('returns channels list with 200 status code', function () {
    // Act & Assert
    getJson('/api/v1/channels')
        ->assertOk();
});

it('returns channels list with correct paginated structure', function () {
    // Act
    $response = getJson('/api/v1/channels');

    // Assert
    $response->assertOk();
    $this->assertPaginatedResponse($response);
});

it('returns channels list with correct channel data structure', function () {
    // Act & Assert
    getJson('/api/v1/channels')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => $this->getChannelStructure(),
            ],
        ]);
});

it('returns channels list respecting limit parameter', function () {
    // Act & Assert
    getJson('/api/v1/channels?limit=1')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('returns channels list respecting page parameter', function () {
    // Act & Assert
    getJson('/api/v1/channels?page=1&limit=1')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 1);
});

it('returns single channel by id with 200 status code', function () {
    // Arrange
    $channel = Channel::first();

    // Act & Assert
    getJson('/api/v1/channels/' . $channel->id)
        ->assertOk();
});

it('returns single channel with correct data structure', function () {
    // Arrange
    $channel = Channel::first();

    // Act & Assert
    getJson('/api/v1/channels/' . $channel->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => $this->getChannelStructure(),
        ])
        ->assertJsonPath('data.id', $channel->id);
});

it('returns 404 for non-existent channel', function () {
    // Act & Assert
    getJson('/api/v1/channels/999999')
        ->assertNotFound();
});

it('can filter channels by sort parameter', function () {
    // Act & Assert
    getJson('/api/v1/channels?sort=id&order=desc')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id'],
            ],
        ]);
});

it('can filter channels by id parameter', function () {
    // Arrange
    $channel = Channel::first();

    // Act & Assert
    getJson('/api/v1/channels?id=' . $channel->id)
        ->assertOk()
        ->assertJsonPath('data.0.id', $channel->id);
});

it('returns channels without pagination when pagination=0', function () {
    // Act & Assert
    getJson('/api/v1/channels?pagination=0')
        ->assertOk()
        ->assertJsonStructure([
            'data',
        ]);
});

it('returns channel with locales array', function () {
    // Arrange
    $channel = Channel::first();

    // Act & Assert
    getJson('/api/v1/channels/' . $channel->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'locales' => [
                    '*' => [
                        'id',
                        'code',
                        'name',
                    ],
                ],
            ],
        ]);
});

it('returns channel with currencies array', function () {
    // Arrange
    $channel = Channel::first();

    // Act & Assert
    getJson('/api/v1/channels/' . $channel->id)
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'currencies' => [
                    '*' => [
                        'id',
                        'code',
                        'name',
                    ],
                ],
            ],
        ]);
});
