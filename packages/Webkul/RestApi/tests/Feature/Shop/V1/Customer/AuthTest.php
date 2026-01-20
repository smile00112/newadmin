<?php

use Illuminate\Support\Facades\Hash;
use Webkul\Customer\Models\Customer;
use Webkul\Faker\Helpers\Customer as CustomerFaker;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('can login customer with valid credentials', function () {
    // Arrange
    $password = 'password123';
    $customer = (new CustomerFaker)->factory()->create([
        'password' => Hash::make($password),
    ]);

    // Act & Assert
    postJson('/api/v1/customer/login', [
        'email'       => $customer->email,
        'password'    => $password,
        'device_name' => 'test-device',
    ])
        ->assertOk()
        ->assertJsonStructure([
            'data' => $this->getCustomerStructure(),
            'message',
            'token',
        ]);
});

it('returns 401 for invalid login credentials', function () {
    // Arrange
    $customer = (new CustomerFaker)->factory()->create();

    // Act & Assert
    postJson('/api/v1/customer/login', [
        'email'       => $customer->email,
        'password'    => 'wrong-password',
        'device_name' => 'test-device',
    ])
        ->assertUnauthorized();
});

it('returns 422 for missing login credentials', function () {
    // Act & Assert
    postJson('/api/v1/customer/login', [])
        ->assertUnprocessable();
});

it('can register new customer', function () {
    // Arrange
    $customerData = [
        'first_name'            => 'John',
        'last_name'             => 'Doe',
        'email'                 => 'johndoe' . time() . '@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ];

    // Act & Assert
    postJson('/api/v1/customer/register', $customerData)
        ->assertOk()
        ->assertJsonStructure([
            'message',
        ]);
});

it('returns 422 for registration with existing email', function () {
    // Arrange
    $customer = (new CustomerFaker)->factory()->create();

    $customerData = [
        'first_name'            => 'John',
        'last_name'             => 'Doe',
        'email'                 => $customer->email,
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ];

    // Act & Assert
    postJson('/api/v1/customer/register', $customerData)
        ->assertUnprocessable();
});

it('returns 422 for registration with invalid data', function () {
    // Arrange
    $customerData = [
        'first_name' => '',
        'last_name'  => '',
        'email'      => 'invalid-email',
        'password'   => '123',
    ];

    // Act & Assert
    postJson('/api/v1/customer/register', $customerData)
        ->assertUnprocessable();
});

it('can send forgot password email', function () {
    // Arrange
    $customer = (new CustomerFaker)->factory()->create();

    // Act & Assert
    postJson('/api/v1/customer/forgot-password', [
        'email' => $customer->email,
    ])
        ->assertOk()
        ->assertJsonStructure([
            'message',
        ]);
});

it('returns error for forgot password with non-existent email', function () {
    // Act & Assert
    postJson('/api/v1/customer/forgot-password', [
        'email' => 'nonexistent@example.com',
    ])
        ->assertStatus(400);
});

it('returns 422 for forgot password with invalid email', function () {
    // Act & Assert
    postJson('/api/v1/customer/forgot-password', [
        'email' => 'invalid-email',
    ])
        ->assertUnprocessable();
});

it('returns 401 for get customer without authentication', function () {
    // Act & Assert
    getJson('/api/v1/customer/get')
        ->assertUnauthorized();
});

it('returns authenticated customer data', function () {
    // Arrange
    $customer = $this->loginAsCustomer();

    // Act & Assert
    getJson('/api/v1/customer/get')
        ->assertOk()
        ->assertJsonStructure([
            'data' => $this->getCustomerStructure(),
        ])
        ->assertJsonPath('data.id', $customer->id);
});

it('returns 401 for logout without authentication', function () {
    // Act & Assert
    deleteJson('/api/v1/customer/logout')
        ->assertUnauthorized();
});

it('can logout authenticated customer', function () {
    // Arrange
    $this->loginAsCustomer();

    // Act & Assert
    deleteJson('/api/v1/customer/logout')
        ->assertOk()
        ->assertJsonStructure([
            'message',
        ]);
});

it('can update customer profile', function () {
    // Arrange
    $customer = $this->loginAsCustomer();

    $updateData = [
        'first_name'       => 'Updated',
        'last_name'        => 'Name',
        'email'            => $customer->email,
        'gender'           => 'Male',
        'current_password' => 'admin123',
    ];

    // Act & Assert
    postJson('/api/v1/customer/profile', $updateData)
        ->assertOk()
        ->assertJsonStructure([
            'data' => $this->getCustomerStructure(),
            'message',
        ]);
});

it('returns 401 for update profile without authentication', function () {
    // Act & Assert
    postJson('/api/v1/customer/profile', [
        'first_name' => 'Test',
    ])
        ->assertUnauthorized();
});

it('returns 422 for update profile with invalid data', function () {
    // Arrange
    $this->loginAsCustomer();

    // Act & Assert
    postJson('/api/v1/customer/profile', [
        'email' => 'invalid-email',
    ])
        ->assertUnprocessable();
});
