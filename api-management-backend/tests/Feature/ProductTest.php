<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function makeProduct(array $overrides = []): Product
{
    return Product::factory()->create(array_merge([
        'title' => 'Matcha Latte',
        'description' => 'A creamy matcha latte.',
        'image_url' => 'regular_matcha.png',
        'price' => 4.50,
        'category' => 'Matcha',
    ], $overrides));
}

function validProductPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Strawberry Matcha',
        'description' => 'Matcha with strawberry.',
        'image_url' => 'strawberry_matcha.png',
        'price' => 5.50,
        'category' => 'Matcha',
    ], $overrides);
}

test('anyone can list products', function () {
    makeProduct();
    makeProduct(['title' => 'Americano', 'category' => 'Coffee']);

    $this->getJson('/api/products')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'image_url',
                    'price',
                    'category',
                ],
            ],
            'links',
            'meta' => ['current_page', 'per_page', 'total'],
        ])
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 2);
});

test('the product list can be filtered by category and search', function () {
    makeProduct(['title' => 'Regular Matcha', 'category' => 'Matcha']);
    makeProduct(['title' => 'Iced Latte', 'category' => 'Coffee']);

    $this->getJson('/api/products?category=matcha')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Regular Matcha');

    $this->getJson('/api/products?search=latte')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Iced Latte');
});

test('anyone can view a single product', function () {
    $product = makeProduct();

    $this->getJson("/api/products/{$product->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.price', '4.50');
});

test('a missing product returns 404', function () {
    $this->getJson('/api/products/999999')->assertNotFound();
});

test('guests cannot create update or delete products', function () {
    $product = makeProduct();

    $this->postJson('/api/products', validProductPayload())->assertUnauthorized();
    $this->patchJson("/api/products/{$product->id}", ['price' => 9.99])->assertUnauthorized();
    $this->deleteJson("/api/products/{$product->id}")->assertUnauthorized();
});

test('signed-in customers cannot create update or delete products', function () {
    Sanctum::actingAs(User::factory()->create());
    $product = makeProduct();

    $this->postJson('/api/products', validProductPayload())
        ->assertForbidden()
        ->assertJsonPath('message', 'Administrator access is required.');

    $this->patchJson("/api/products/{$product->id}", ['price' => 9.99])
        ->assertForbidden();

    $this->deleteJson("/api/products/{$product->id}")->assertForbidden();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'price' => '4.50',
    ]);
});

test('an administrator can create a product', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson('/api/products', validProductPayload())
        ->assertCreated()
        ->assertJsonPath('data.title', 'Strawberry Matcha');

    $this->assertDatabaseHas('products', [
        'title' => 'Strawberry Matcha',
        'category' => 'Matcha',
    ]);
});

test('creating a product validates the input', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson('/api/products', [
        'price' => 'not-a-number',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'price', 'category']);
});

test('an administrator can update a product', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $product = makeProduct();

    $this->patchJson("/api/products/{$product->id}", [
        'price' => 9.99,
    ])->assertOk()
        ->assertJsonPath('data.price', '9.99');

    expect($product->refresh()->price)->toBe('9.99');
});

test('updating a product validates the input', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $product = makeProduct();

    $this->patchJson("/api/products/{$product->id}", [
        'price' => -1,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('price');
});

test('an administrator can delete a product', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $product = makeProduct();

    $this->deleteJson("/api/products/{$product->id}")->assertNoContent();

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('per_page must be a positive integer under the limit', function (string $value) {
    $this->getJson("/api/products?per_page={$value}")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('per_page');
})->with(['abc', '0', '-1', '101']);

test('per_page controls the page size', function () {
    makeProduct(['title' => 'Product 1']);
    makeProduct(['title' => 'Product 2']);
    makeProduct(['title' => 'Product 3']);
    makeProduct(['title' => 'Product 4']);
    makeProduct(['title' => 'Product 5']);
    makeProduct(['title' => 'Product 6']);

    $this->getJson('/api/products?per_page=5')
        ->assertOk()
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.total', 6)
        ->assertJsonCount(5, 'data');
});

test('the product factory creates valid catalog rows', function () {
    $product = Product::factory()->matcha()->create();

    expect($product->exists)->toBeTrue()
        ->and($product->category)->toBe('Matcha')
        ->and($product->price)->toBeString();
});
