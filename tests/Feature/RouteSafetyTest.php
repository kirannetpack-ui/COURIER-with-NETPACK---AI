<?php

namespace Tests\Feature;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

class RouteSafetyTest extends TestCase
{
    public function test_named_routes_are_unique(): void
    {
        $names = [];

        foreach (RouteFacade::getRoutes() as $route) {
            $name = $route->getName();
            if ($name !== null) {
                $names[] = $name;
            }
        }

        $duplicates = array_keys(array_filter(array_count_values($names), fn (int $count): bool => $count > 1));

        $this->assertSame([], $duplicates, 'Duplicate route names: '.implode(', ', $duplicates));
    }

    public function test_state_changing_routes_do_not_accept_get_requests(): void
    {
        $routeNames = [
            'rider.orders.accept',
            'rider.orders.reject',
            'rider.orders.pickup',
            'rider.orders.in-transit',
            'rider.orders.out-for-delivery',
            'international.partners.toggle',
            'international.surcharges.toggle',
            'international.transit-points.toggle',
            'overseas.transit-points.toggle',
        ];

        foreach ($routeNames as $routeName) {
            $route = RouteFacade::getRoutes()->getByName($routeName);

            $this->assertInstanceOf(Route::class, $route, "Missing route [{$routeName}].");
            $this->assertNotContains('GET', $route->methods(), "Route [{$routeName}] must not change state via GET.");
        }
    }

    public function test_role_specific_routes_include_role_middleware(): void
    {
        $expectedMiddleware = [
            'seller.dashboard' => 'role:seller',
            'rider.dashboard' => 'role:rider',
            'partner.dashboard' => 'role:partner',
            'international.dashboard' => 'role:super_admin,admin,international_admin,staff',
            'overseas.dashboard' => 'role:overseas',
        ];

        foreach ($expectedMiddleware as $routeName => $middleware) {
            $route = RouteFacade::getRoutes()->getByName($routeName);

            $this->assertInstanceOf(Route::class, $route, "Missing route [{$routeName}].");
            $this->assertContains($middleware, $route->gatherMiddleware(), "Route [{$routeName}] is missing role protection.");
        }
    }
}
