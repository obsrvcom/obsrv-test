<?php

use App\Models\User;
use App\Models\Company;
use Tests\SubdomainTestTrait;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class, SubdomainTestTrait::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users without company selection are redirected to company selection', function () {
    $user = User::factory()->create();
    $this->withoutSubdomainMiddleware();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertRedirect('/company/select');
});

test('authenticated users with company selection can visit the dashboard', function () {
    $user = User::factory()->create();
    $company = $this->setupTestWithCompany($user);

    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});
