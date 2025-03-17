<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the root route returns the welcome view.
     *
     * @return void
     */
    public function test_welcome_route_returns_welcome_view()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
    }

    /**
     * Test that the products index route is accessible and returns a 200 status code.
     *
     * @return void
     */
    public function test_products_index_route_is_accessible()
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
    }

    /**
     * Test that the product creation form route is accessible.
     *
     * @return void
     */
    public function test_products_create_route_is_accessible()
    {
        $response = $this->get('/products/create');

        $response->assertStatus(200);
        $response->assertViewIs('products.create');
    }

    /**
     * Test that a specific product can be viewed.
     *
     * @return void
     */
    public function test_products_show_route_is_accessible()
    {
        $product = Product::factory()->create();

        $response = $this->get("/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertViewIs('products.show');
        $response->assertViewHas('product', $product);
    }

    /**
     * Test that the product edit form route is accessible.
     *
     * @return void
     */
    public function test_products_edit_route_is_accessible()
    {
        $product = Product::factory()->create();

        $response = $this->get("/products/{$product->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('products.edit');
        $response->assertViewHas('product', $product);
    }

    /**
     * Test that a new product can be created via the store route.
     *
     * @return void
     */
    public function test_products_store_route_creates_new_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'quantity' => 10
        ];

        $response = $this->post('/products', $productData);

        $response->assertStatus(302); // Redirect after successful creation
        $this->assertDatabaseHas('products', $productData);
    }

    /**
     * Test that an existing product can be updated.
     *
     * @return void
     */
    public function test_products_update_route_updates_existing_product()
    {
        $product = Product::factory()->create();
        
        $updatedData = [
            'name' => 'Updated Product Name',
            'description' => 'Updated product description',
            'price' => 149.99,
            'quantity' => 20
        ];

        $response = $this->put("/products/{$product->id}", $updatedData);

        $response->assertStatus(302); // Redirect after successful update
        $this->assertDatabaseHas('products', array_merge(['id' => $product->id], $updatedData));
    }

    /**
     * Test that an existing product can be deleted.
     *
     * @return void
     */
    public function test_products_destroy_route_deletes_product()
    {
        $product = Product::factory()->create();

        $response = $this->delete("/products/{$product->id}");

        $response->assertStatus(302); // Redirect after successful deletion
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Test that requesting a non-existent product returns a 404 error.
     *
     * @return void
     */
    public function test_products_show_route_returns_404_for_invalid_id()
    {
        $nonExistentId = 9999;
        
        $response = $this->get("/products/{$nonExistentId}");

        $response->assertStatus(404);
    }

    /**
     * Test that validation errors are returned when required fields are missing.
     *
     * @return void
     */
    public function test_products_store_route_validates_required_fields()
    {
        // Sending empty data to trigger validation errors
        $response = $this->post('/products', []);

        $response->assertStatus(302); // Redirect back with errors
        $response->assertSessionHasErrors(['name', 'price']); // Assuming these are required fields
    }
}