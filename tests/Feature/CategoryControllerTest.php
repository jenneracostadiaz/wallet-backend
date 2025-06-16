<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Categories', function () {
    it('can list categories for authenticated user', function () {
        $user = User::factory()->create();
        Category::factory()->count(3)->for($user)->create();

        $this->actingAs($user)
            ->getJson('/api/categories')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('can show a single category', function () {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)
            ->getJson("/api/categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $category->id);
    });

    it('can create a category', function () {
        $user = User::factory()->create();
        $payload = [
            'name' => 'Test Category',
            'type' => 'income',
        ];

        $this->actingAs($user)
            ->postJson('/api/categories', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Test Category');
    });

    it('can update a category', function () {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();
        $payload = ['name' => 'Updated Category', 'type' => 'expense'];

        $this->actingAs($user)
            ->putJson("/api/categories/{$category->id}", $payload)
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Category');
    });

    it('can delete a category', function () {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)
            ->deleteJson("/api/categories/{$category->id}")
            ->assertOk()
            ->assertJson(['message' => 'Category deleted successfully']);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    });

    it('cannot access another user\'s category', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $category = Category::factory()->for($other)->create();

        $this->actingAs($user)
            ->getJson("/api/categories/{$category->id}")
            ->assertForbidden();
    });
});
