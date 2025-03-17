<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Illuminate\Support\Facades\File;

class RouteTest extends TestCase
{
    /**
     * Unit test to verify that the welcome view file exists.
     *
     * @return void
     */
    public function test_welcome_route_view_exists()
    {
        $this->assertTrue(File::exists(resource_path('views/welcome.blade.php')));
    }

    /**
     * Unit test to verify that all 7 resource routes are properly registered.
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

        $registeredRoutes = array_keys(Route::getRoutes()->getRoutesByName());
        
        foreach ($expectedRoutes as $route) {
            $this->assertContains($route, $registeredRoutes, "Route {$route} is not registered");
        }
    }
}