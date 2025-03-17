<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if index method displays paginated list of products
     *
     * @return void
     */
    public function test_index_displays_paginated_products(): void
    {
        // Create 5 products in database
        Product::factory()->count(5)->create();

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products');
        
        // Check pagination shows 4 products per page
        $products = $response->viewData('products');
        $this->assertEquals(4, $products->count());
        $this->assertEquals(5, $products->total());
    }

    /**
     * Test if create method returns correct view
     *
     * @return void
     */
    public function test_create_shows_product_form(): void
    {
        $response = $this->get(route('products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('products.create');
    }

    /**
     * Test storing a valid product
     *
     * @return void
     */
    public function test_store_valid_product(): void
    {
        $productData = [
            'code' => 'PROD001',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 99.99,
            'description' => 'Test description'
        ];

        $response = $this->post(route('products.store'), $productData);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'New product is added successfully.');
        $this->assertDatabaseHas('products', $productData);
    }

    /**
     * Test storing product with invalid data
     *
     * @return void
     */
    public function test_store_invalid_product(): void
    {
        $invalidData = [
            'code' => '',
            'name' => '',
            'quantity' => 0,
            'price' => ''
        ];

        $response = $this->post(route('products.store'), $invalidData);

        $response->assertSessionHasErrors(['code', 'name', 'price']);
        $this->assertDatabaseMissing('products', $invalidData);
    }

    /**
     * Test if show method displays correct product
     *
     * @return void
     */
    public function test_show_displays_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertViewIs('products.show');
        $response->assertViewHas('product', $product);
    }

    /**
     * Test if edit method shows form with product data
     *
     * @return void
     */
    public function test_edit_displays_product_form(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('products.edit', $product));

        $response->assertStatus(200);
        $response->assertViewIs('products.edit');
        $response->assertViewHas('product', $product);
    }

    /**
     * Test updating product with valid data
     *
     * @return void
     */
    public function test_update_valid_product(): void
    {
        $product = Product::factory()->create();
        
        $updatedData = [
            'code' => 'PROD002',
            'name' => 'Updated Product',
            'quantity' => 200,
            'price' => 199.99,
            'description' => 'Updated description'
        ];

        $response = $this->put(route('products.update', $product), $updatedData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Product is updated successfully.');
        $this->assertDatabaseHas('products', $updatedData);
    }

    /**
     * Test updating product with invalid data
     *
     * @return void
     */
    public function test_update_invalid_product(): void
    {
        $product = Product::factory()->create();
        
        $invalidData = [
            'code' => '',
            'name' => '',
            'quantity' => -1,
            'price' => 'invalid'
        ];

        $response = $this->put(route('products.update', $product), $invalidData);

        $response->assertSessionHasErrors(['code', 'name', 'quantity', 'price']);
        $this->assertDatabaseMissing('products', $invalidData);
    }

    /**
     * Test if destroy method deletes product
     *
     * @return void
     */
    public function test_destroy_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Product is deleted successfully.');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Test storing product with duplicate code
     *
     * @return void
     */
    public function test_store_duplicate_product_code(): void
    {
        // Create a product with a specific code
        Product::factory()->create(['code' => 'EXISTING_CODE']);

        // Try to create another product with the same code
        $productData = [
            'code' => 'EXISTING_CODE',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 99.99
        ];

        $response = $this->post(route('products.store'), $productData);

        $response->assertSessionHasErrors('code');
    }

    /**
     * Test quantity exceeding maximum allowed value
     *
     * @return void
     */
    public function test_quantity_bounds(): void
    {
        $productData = [
            'code' => 'PROD003',
            'name' => 'Test Product',
            'quantity' => 10001,
            'price' => 99.99
        ];

        $response = $this->post(route('products.store'), $productData);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseMissing('products', $productData);
    }
}