<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class RouteTest extends TestCase
{
    /**
     * Unit test to verify that the welcome view file exists
     *
     * @return void
     */
    public function test_welcome_route_view_exists()
    {
        $this->assertTrue(View::exists('welcome'));
    }

    /**
     * Unit test to verify that all 7 resource routes are properly registered
     *
     * @return void
     */
    public function test_resource_routes_are_registered()
    {
        $expectedRoutes = [
            'products.index',
            'products.create',
            'products.store',
            'products.show',
            'products.edit',
            'products.update',
            'products.destroy'
        ];

        foreach ($expectedRoutes as $route) {
            $this->assertTrue(Route::has($route), "Route {$route} is not defined");
        }
    }
}