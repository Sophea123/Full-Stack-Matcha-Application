<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminUser(): User
{
    return User::factory()->admin()->create();
}

test('the home page redirects to the admin dashboard', function () {
    $this->get('/')->assertRedirect('/admin');
});

test('guests are redirected to the admin login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/products')->assertRedirect('/admin/login');
    $this->get('/admin/products/create')->assertRedirect('/admin/login');
});

test('the admin login page renders', function () {
    $this->get('/admin/login')
        ->assertOk()
        ->assertSee('Brewly Admin')
        ->assertSee('Sign in');
});

test('customers cannot sign into the admin dashboard', function () {
    User::factory()->create([
        'email' => 'customer@example.com',
        'password' => 'password123',
    ]);

    $this->post('/admin/login', [
        'email' => 'customer@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('administrators can sign in and see the dashboard', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'Owner',
        'email' => 'admin@example.com',
        'password' => 'password123',
    ]);

    $this->post('/admin/login', [
        'email' => 'admin@example.com',
        'password' => 'password123',
    ])->assertRedirect('/admin');

    $this->assertAuthenticatedAs($admin);

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Products')
        ->assertSee('Owner');
});

test('signed-in customers cannot open admin pages', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();

    $this->actingAs(User::factory()->create())
        ->post('/admin/products', [
            'title' => 'Hacked',
            'price' => 1,
            'category' => 'Matcha',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('products', ['title' => 'Hacked']);
});

test('the product list shows and filters catalog rows', function () {
    $admin = adminUser();
    Product::factory()->create(['title' => 'Mango Matcha', 'category' => 'Matcha']);
    Product::factory()->create(['title' => 'Iced Latte', 'category' => 'Coffee']);

    $this->actingAs($admin)
        ->get('/admin/products')
        ->assertOk()
        ->assertSee('Mango Matcha')
        ->assertSee('Iced Latte');

    $this->actingAs($admin)
        ->get('/admin/products?category=coffee')
        ->assertOk()
        ->assertSee('Iced Latte')
        ->assertDontSee('Mango Matcha');

    $this->actingAs($admin)
        ->get('/admin/products?search=mango')
        ->assertOk()
        ->assertSee('Mango Matcha')
        ->assertDontSee('Iced Latte');
});

test('the product list paginates with a configurable page size', function () {
    $admin = adminUser();

    foreach (range(1, 7) as $number) {
        Product::factory()->create(['title' => "Catalog Item {$number}"]);
    }

    $this->actingAs($admin)
        ->get('/admin/products')
        ->assertOk()
        ->assertSee('Catalog Item 7')
        ->assertSee('Catalog Item 3')
        ->assertDontSee('Catalog Item 2')
        ->assertDontSee('Catalog Item 1')
        ->assertSee('Next');

    $this->actingAs($admin)
        ->get('/admin/products?page=2')
        ->assertOk()
        ->assertSee('Catalog Item 2')
        ->assertSee('Catalog Item 1')
        ->assertDontSee('Catalog Item 7')
        ->assertSee('Prev');

    $this->actingAs($admin)
        ->get('/admin/products?per_page=25')
        ->assertOk()
        ->assertSee('Catalog Item 7')
        ->assertSee('Catalog Item 1')
        ->assertSee('Page 1 of 1');

    $this->actingAs($admin)
        ->get('/admin/products?per_page=999')
        ->assertOk()
        ->assertDontSee('Catalog Item 2');
});

test('administrators can create a product', function () {
    $this->actingAs(adminUser())
        ->post('/admin/products', [
            'title' => 'Mango Matcha',
            'description' => 'Fresh mango blended with matcha.',
            'image_url' => 'regular_matcha.png',
            'price' => '5.25',
            'category' => 'Matcha',
        ])
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('products', [
        'title' => 'Mango Matcha',
        'price' => '5.25',
        'category' => 'Matcha',
    ]);
});

test('product validation is enforced in the dashboard', function () {
    $this->actingAs(adminUser())
        ->post('/admin/products', [
            'price' => 'not-a-number',
        ])
        ->assertSessionHasErrors(['title', 'price', 'category']);

    $this->assertDatabaseCount('products', 0);
});

test('administrators can update a product', function () {
    $product = Product::factory()->create(['title' => 'Old name', 'price' => 4]);
    $admin = adminUser();

    $this->actingAs($admin)
        ->put(route('admin.products.update', $product), [
            'title' => 'New name',
            'description' => 'Updated description.',
            'image_url' => 'cream.jpeg',
            'price' => '7.50',
            'category' => 'Dessert',
        ])
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('status');

    $product->refresh();

    expect($product->title)->toBe('New name')
        ->and($product->price)->toBe('7.50')
        ->and($product->category)->toBe('Dessert');
});

test('administrators can delete a product', function () {
    $product = Product::factory()->create(['title' => 'Temporary item']);
    $admin = adminUser();

    $this->actingAs($admin)
        ->delete(route('admin.products.destroy', $product))
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('administrators can sign out', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post('/admin/logout')
        ->assertRedirect(route('admin.login'));

    $this->assertGuest();
});
