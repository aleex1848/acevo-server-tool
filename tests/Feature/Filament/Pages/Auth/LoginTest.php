<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

test('an unauthenticated user can access the login page', function () {
    auth()->logout();

    $this->get(Filament::getLoginUrl())
        ->assertOk();
});

test('an unauthenticated user can not access the admin panel', function () {
    auth()->logout();

    $this->get('admin')
        ->assertRedirect(Filament::getLoginUrl());
});

test('an unauthenticated user can login', function () {
    auth()->logout();

    livewire(Login::class)
        ->fillForm([
            'email' => config('app.default_user.email'),
            'password' => config('app.default_user.password'),
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();
});

test('an authenticated user can access the admin panel', function () {
    $this->get('admin')
        ->assertOk();
});

test('a non-admin user cannot access the admin panel', function (): void {
    auth()->logout();

    $this->actingAs(User::factory()->create(['is_admin' => false]));

    $this->get('admin')->assertForbidden();
});

test('an authenticated user can logout', function () {
    $this->assertAuthenticated();

    $this->post(Filament::getLogoutUrl())
        ->assertRedirect(Filament::getLoginUrl());
});
