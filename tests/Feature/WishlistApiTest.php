<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// Import Sanctum for actingAs

class WishlistApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product1;
    protected Product $product2;

    public function test_authenticated_user_can_view_empty_wishlist(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/wishlist');

        $response->assertOk();
        $response->assertJsonCount(0, 'data'); // Expecting an empty 'data' array
        $response->assertJson(['data' => []]);
    }


    public function test_authenticated_user_can_view_wishlist_with_items(): void
    {
        Sanctum::actingAs($this->user);
        $this->user->wishedProducts()->attach($this->product1->id);
        $this->user->wishedProducts()->attach($this->product2->id);

        $response = $this->getJson('/api/v1/wishlist');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $this->product1->id]);
        $response->assertJsonFragment(['id' => $this->product2->id]);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'description', 'price']
            ]
        ]);
    }

    public function test_unauthenticated_user_cannot_view_wishlist(): void
    {
        $response = $this->getJson('/api/v1/wishlist');
        $response->assertUnauthorized(); // Expect 401
    }

    public function test_authenticated_user_can_add_product_to_wishlist(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/wishlist', ['product_id' => $this->product1->id]);

        $response->assertCreated(); // Expect 201
        $response->assertJson(['message' => 'Product added to wishlist successfully.']);
        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
        ]);
    }


    public function test_authenticated_user_cannot_add_same_product_to_wishlist_twice(): void
    {
        Sanctum::actingAs($this->user);
        $this->user->wishedProducts()->attach($this->product1->id); // Product already in wishlist

        $response = $this->postJson('/api/v1/wishlist', ['product_id' => $this->product1->id]);

        $response->assertStatus(409); // Expect 409 Conflict
        $response->assertJson(['message' => 'Product already in wishlist.']);
        // Ensure it's still there only once
        $this->assertEquals(1, $this->user->wishedProducts()->where('product_id', $this->product1->id)->count());
    }

    public function test_authenticated_user_cannot_add_non_existent_product_to_wishlist(): void
    {
        Sanctum::actingAs($this->user);
        $nonExistentProductId = 9999;

        $response = $this->postJson('/api/v1/wishlist', ['product_id' => $nonExistentProductId]);

        $response->assertStatus(422); // Unprocessable Entity due to validation failure
        $response->assertJsonValidationErrors(['product_id']);
    }

    public function test_authenticated_user_cannot_add_to_wishlist_without_product_id(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/wishlist', []); // Missing product_id

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id']);
    }

    public function test_unauthenticated_user_cannot_add_product_to_wishlist(): void
    {
        $response = $this->postJson('/api/v1/wishlist', ['product_id' => $this->product1->id]);
        $response->assertUnauthorized(); // Expect 401
    }

    public function test_authenticated_user_can_remove_product_from_wishlist(): void
    {
        Sanctum::actingAs($this->user);
        $this->user->wishedProducts()->attach($this->product1->id); // Add product first

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
        ]);

        $response = $this->deleteJson("/api/v1/wishlist/{$this->product1->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('wishlist_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
        ]);
    }


    public function test_authenticated_user_cannot_remove_product_not_in_their_wishlist(): void
    {
        Sanctum::actingAs($this->user);
        // Product 2 is not in the user's wishlist

        $response = $this->deleteJson("/api/v1/wishlist/{$this->product2->id}");

        $response->assertNotFound(); // Expect 404
        $response->assertJson(['message' => 'Product not found in wishlist.']);
    }

    public function test_authenticated_user_cannot_remove_non_existent_product_from_wishlist(): void
    {
        Sanctum::actingAs($this->user);
        $nonExistentProductId = 9999;

        $response = $this->deleteJson("/api/v1/wishlist/{$nonExistentProductId}");

        $response->assertNotFound();
        $response->assertJson(['message' => 'Product not found in wishlist.']);
    }

    public function test_unauthenticated_user_cannot_remove_product_from_wishlist(): void
    {
        $response = $this->deleteJson("/api/v1/wishlist/{$this->product1->id}");
        $response->assertUnauthorized(); // Expect 401
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->product1 = Product::factory()->create();
        $this->product2 = Product::factory()->create();
    }
}
