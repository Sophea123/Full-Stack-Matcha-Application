<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('guests cannot open the account manager', function () {
    $this->get('/admin/users')->assertRedirect('/admin/login');
    $this->get('/admin/users/create')->assertRedirect('/admin/login');
});

test('customers cannot manage accounts', function () {
    $customer = User::factory()->create(['name' => 'Normal Customer']);
    $other = User::factory()->create();

    $this->actingAs($customer)->get('/admin/users')->assertForbidden();
    $this->actingAs($customer)->get('/admin/users/create')->assertForbidden();

    $this->actingAs($customer)
        ->post('/admin/users', [
            'name' => 'Sneaky Admin',
            'email' => 'sneaky@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => '1',
        ])
        ->assertForbidden();

    $this->actingAs($customer)
        ->delete(route('admin.users.destroy', $other))
        ->assertForbidden();

    $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.com']);
    $this->assertDatabaseHas('users', ['id' => $other->id]);
});

test('the account list shows admins and customers with totals', function () {
    $admin = User::factory()->admin()->create(['name' => 'Owner Account']);
    User::factory()->create(['name' => 'Regular Person', 'email' => 'regular@example.com']);

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('Users & admins')
        ->assertSee('Owner Account')
        ->assertSee('Regular Person')
        ->assertSee('Administrator')
        ->assertSee('Customer')
        ->assertSee('All accounts')
        ->assertSee('Add account');
});

test('the account list can be searched and filtered by role', function () {
    $admin = User::factory()->admin()->create(['name' => 'Site Owner', 'email' => 'boss@example.com']);
    User::factory()->create(['name' => 'Regular Person', 'email' => 'regular@example.com']);
    User::factory()->create(['name' => 'Hidden Person', 'email' => 'hidden@example.com']);

    $this->actingAs($admin)
        ->get('/admin/users?search=regular')
        ->assertOk()
        ->assertSee('regular@example.com')
        ->assertDontSee('hidden@example.com')
        ->assertSee('1 account(s) found.');

    $this->actingAs($admin)
        ->get('/admin/users?role=customer')
        ->assertOk()
        ->assertSee('regular@example.com')
        ->assertSee('hidden@example.com')
        ->assertSee('2 account(s) found.');

    $this->actingAs($admin)
        ->get('/admin/users?role=admin')
        ->assertOk()
        ->assertSee('boss@example.com')
        ->assertDontSee('hidden@example.com')
        ->assertSee('1 account(s) found.');
});

test('administrators can create another administrator', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'New Owner',
            'email' => 'NewOwner@Example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => '1',
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('status');

    $created = User::where('email', 'newowner@example.com')->firstOrFail();

    expect($created->name)->toBe('New Owner')
        ->and($created->is_admin)->toBeTrue()
        ->and(Hash::check('password123', $created->password))->toBeTrue();
});

test('administrators can create a customer account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'New Customer',
            'email' => 'customer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('admin.users.index'));

    $created = User::where('email', 'customer@example.com')->firstOrFail();

    expect($created->is_admin)->toBeFalse();
});

test('account creation validates the email and password', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'Duplicate',
            'email' => 'taken@example.com',
            'password' => 'short',
            'password_confirmation' => 'different',
            'is_admin' => '1',
        ])
        ->assertSessionHasErrors(['email', 'password']);

    expect(User::where('email', 'taken@example.com')->count())->toBe(1);
});

test('administrators can update details, reset a password, and promote a customer', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $customer), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'is_admin' => '1',
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('status');

    $customer->refresh();

    expect($customer->name)->toBe('New Name')
        ->and($customer->email)->toBe('new@example.com')
        ->and($customer->is_admin)->toBeTrue()
        ->and(Hash::check('newpassword123', $customer->password))->toBeTrue();
});

test('an administrator cannot remove their own admin access', function () {
    $admin = User::factory()->admin()->create(['name' => 'Owner']);
    User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.users.update', $admin), [
            'name' => 'Owner',
            'email' => $admin->email,
        ])
        ->assertSessionHasErrors('is_admin');

    expect($admin->refresh()->is_admin)->toBeTrue();
});

test('an administrator cannot delete their own account', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertSessionHasErrors('user');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('administrators can delete another account and its tokens', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();
    $customer->createToken('mobile');

    expect($customer->tokens()->count())->toBe(1);

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $customer))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('users', ['id' => $customer->id]);
    expect(User::find($customer->id))->toBeNull();
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $customer->id,
        'tokenable_type' => User::class,
    ]);
});
