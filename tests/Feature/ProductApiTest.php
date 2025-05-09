<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class ProductApiTest extends TestCase
{
    use RefreshDatabase;


    /**
     * Test fetching all products successfully.
     * @return void
     */
    public function test_returns_successful_response_with_list_of_products(): void
    {

        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'price',
                ]
            ],
            'links' => [],
            'meta' => [],
        ]);

        // Assert that the 'data' array contains 3 items
        $response->assertJsonCount(3, 'data');
    }

    /**
     * Test fetching all products when no products exist.
     * @return void
     */
    public function test_returns_empty_list_if_no_products_exist(): void
    {

        $response = $this->getJson('/api/v1/products');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
        $response->assertJson(['data' => []]);
    }

    /**
     * Test fetching paginated products.
     * @return void
     */
    public function test_returns_paginated_products_if_pagination_is_implemented(): void
    {

        Product::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);
        $response->assertJsonCount(10, 'data');
        $this->assertEquals(10, $response->json('meta.per_page'));
    }

    /**
     * Test fetching a specific product successfully.
     * @return void
     */
    public function test_returns_successful_response_with_specified_product_details(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product Alpha',
            'price' => 1999 // Price in cents or smallest unit
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'price',
            ]
        ]);

        // Assert that the returned product data matches the created product
        $response->assertJson([
            'data' => [
                'id' => $product->id,
                'name' => 'Test Product Alpha',
                'price' => 1999,
            ]
        ]);
    }

    /**
     * Test fetching a non-existent product.
     * @return void
     */
    public function test_returns_404_not_found_if_product_does_not_exist(): void
    {
        $nonExistentProductId = 99999;

        $response = $this->getJson("/api/v1/products/{$nonExistentProductId}");

        $response->assertNotFound();
    }

    /**
     * Test fetching a product with an invalid ID format.
     * @return void
     */
    public function test_returns_404_not_found_for_invalid_product_id_format(): void
    {
        $invalidProductId = 'abc-invalid';

        $response = $this->getJson("/api/v1/products/{$invalidProductId}");

        $response->assertNotFound();
    }
}
