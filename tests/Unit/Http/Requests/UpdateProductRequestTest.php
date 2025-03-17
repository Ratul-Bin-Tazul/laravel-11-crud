<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\Validator;
use Tests\TestCase;

class UpdateProductRequestTest extends TestCase
{
    use WithFaker;

    private UpdateProductRequest $request;
    private Validator $validator;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->product = new Product();
        $this->product->id = 1;
        
        $this->request = new UpdateProductRequest();
        $this->request->setContainer($this->app)->setRedirector($this->app['redirect']);
        $this->request->product = $this->product;
    }

    private function getValidator(array $data): Validator
    {
        return validator($data, $this->request->rules());
    }
    
    /**
     * Test Maximum Boundaries
     */
    public function testMaximumBoundaries(): void
    {
        $data = [
            'code' => 'PROD123',
            'name' => 'Test Product',
            'quantity' => 10000,
            'price' => 99999.99,
            'product' => ['id' => 1],
        ];
        
        $validator = $this->getValidator($data);
        
        $this->assertFalse($validator->fails());
    }

    /**
     * Test Special Characters in Code
     */
    public function testSpecialCharactersInCode(): void
    {
        $data = [
            'code' => 'PROD-123_TEST',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 19.99,
            'product' => ['id' => 1],
        ];
        
        $validator = $this->getValidator($data);
        
        $this->assertFalse($validator->fails());
    }

    /**
     * Test Price Format
     */
    public function testPriceFormat(): void
    {
        $data = [
            'code' => 'PROD123',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => '19.99',
            'product' => ['id' => 1],
        ];
        
        $validator = $this->getValidator($data);
        
        $this->assertFalse($validator->fails());
    }

    /**
     * Test Product Update With HTML in Description
     */
    public function testProductUpdateWithHTMLInDescription(): void
    {
        $data = [
            'code' => 'PROD123',
            'name' => 'Test Product',
            'quantity' => 100,
            'price' => 19.99,
            'description' => '<p>Test</p>',
            'product' => ['id' => 1],
        ];
        
        $validator = $this->getValidator($data);
        
        $this->assertFalse($validator->fails());
    }

    /**
     * Test Minimum Quantity Validation
     */
    public function testMinimumQuantityValidation(): void
    {
        $data = [
            'code' => 'PROD123',
            'name' => 'Test Product',
            'quantity' => 1,
            'price' => 19.99,
            'product' => ['id' => 1],
        ];
        
        $validator = $this->getValidator($data);
        
        $this->assertFalse($validator->fails());
    }
}