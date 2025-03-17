<?php

namespace Tests\Feature\Http\Requests;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateProductRequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Product $product;
    private Product $existingProduct;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        $this->product = Product::create([
            'code' => 'PROD123',
            'name' => 'Original Product',
            'quantity' => 50,
            'price' => 9.99,
            'description' => 'Original description'
        ]);
        
        $this->existingProduct = Product::create([
            'code' => 'EXISTING_CODE',
            'name' => 'Existing Product',
            'quantity' => 30,
            'price' => 15.99,
            'description' => 'Existing product description'
        ]);
    }
    
    /**
     * Test Complete Valid Product Update
     */
    public function testCompleteValidProductUpdate(): void
    {
        $this->actingAs($this->user);
        
        $response = $this->put(route('products.update', $this->product->id), [
            'code' => 'PROD123',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 19.99,
            'description' => 'Test description'
        ]);
        
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'code' => 'PROD123',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 19.99,
            'description' => 'Test description'
        ]);
    }

    /**
     * Test Product Update Without Description
     */
    public function testProductUpdateWithoutDescription(): void
    {
        $this->actingAs($this->user);
        
        $response = $this->put(route('products.update', $this->product->id), [
            'code' => 'PROD123',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 19.99
        ]);
        
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'code' => 'PROD123',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 19.99,
            'description' => null
        ]);
    }

    /**
     * Test Unique Code Validation
     */
    public function testUniqueCodeValidation(): void
    {
        $this->actingAs($this->user);
        
        // Should pass validation because we're using the same code for the same product
        $response = $this->put(route('products.update', $this->product->id), [
            'code' => 'PROD123',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 19.99
        ]);
        
        $response->assertSessionHasNoErrors();
        
        // Should fail validation because we're using another product's code
        $response = $this->put(route('products.update', $this->product->id), [
            'code' => 'EXISTING_CODE',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 19.99
        ]);
        
        $response->assertSessionHasErrors('code');
        
        // But should pass when updating that product with its own code
        $response = $this->put(route('products.update', $this->existingProduct->id), [
            'code' => 'EXISTING_CODE',
            'name' => 'Updated Existing Product',
            'quantity' => 100,
            'price' => 19.99
        ]);
        
        $response->assertSessionHasNoErrors();
    }
}