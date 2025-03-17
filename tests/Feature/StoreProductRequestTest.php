<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class StoreProductRequestTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test Store Product Request Validation
     */
    public function testStoreProductRequestValidation()
    {
        $response = $this->post('/products', [
            'code' => 'PROD123',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 99.99,
            'description' => 'Test description'
        ]);
        
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'code' => 'PROD123',
            'name' => 'Test Product'
        ]);
    }
    
    /**
     * Test Form Validation With Missing Required Fields
     */
    public function testFormValidationWithMissingRequiredFields()
    {
        $response = $this->post('/products', [
            'code' => '',
            'name' => '',
            'quantity' => null,
            'price' => null
        ]);
        
        $response->assertSessionHasErrors(['code', 'name', 'quantity', 'price']);
    }
    
    /**
     * Test Product Creation Route
     */
    public function testProductCreationRoute()
    {
        $response = $this->get('/products/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('products.create');
    }
    
    /**
     * Test Store Product Route
     */
    public function testStoreProductRoute()
    {
        $response = $this->post('/products', [
            'code' => 'PROD123',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 99.99,
            'description' => 'Test description'
        ]);
        
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');
    }
    
    /**
     * Test Code Uniqueness Validation
     */
    public function testCodeUniquenessValidation()
    {
        // First create a product with the code
        Product::create([
            'code' => 'EXISTING_CODE',
            'name' => 'Existing Product',
            'quantity' => 50,
            'price' => 49.99
        ]);
        
        // Try to create another product with the same code
        $response = $this->post('/products', [
            'code' => 'EXISTING_CODE',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 99.99
        ]);
        
        $response->assertSessionHasErrors('code');
        $this->assertEquals(1, Product::where('code', 'EXISTING_CODE')->count());
    }
    
    /**
     * Test CSRF Protection
     */
    public function testCSRFProtection()
    {
        // Disable the CSRF middleware for this test
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        
        // Make a POST request without a CSRF token by using an HTTP client
        $client = new \GuzzleHttp\Client([
            'base_uri' => config('app.url'),
            'http_errors' => false,
        ]);
        
        $response = $client->post('/products', [
            'form_params' => [
                'code' => 'PROD127',
                'name' => 'Test Product',
                'quantity' => 100,
                'price' => 99.99
            ]
        ]);
        
        // Should get a 419 (CSRF token mismatch) status code
        $this->assertEquals(419, $response->getStatusCode());
    }
}