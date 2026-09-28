<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersistentSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_user_on_rates_inquiry_has_persistent_sidebar_and_sign_in(): void
    {
        $response = $this->get('/rates/inquiry');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Must have sidebar container
        $this->assertStringContainsString('<aside', $content);
        $this->assertStringContainsString('Client Portal', $content);
        $this->assertStringContainsString('Rate Calculator', $content);
        $this->assertStringContainsString('Sign In', $content);
        $this->assertStringContainsString('Create Account', $content);

        // Must not contain x-show on the aside tag
        $this->assertFalse(
            (bool) preg_match('/<aside[^>]+x-show="sidebarOpen"/', $content),
            'Sidebar <aside> must not have x-show="sidebarOpen" which hides it on desktop'
        );
    }

    public function test_authenticated_client_has_persistent_sidebar_and_sign_out(): void
    {
        $client = User::factory()->create([
            'role' => 'customer',
            'user_type' => 'client',
        ]);

        $response = $this->actingAs($client)->get('/rates/inquiry');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('<aside', $content);
        $this->assertStringContainsString('Client Portal', $content);
        $this->assertStringContainsString('Sign Out', $content);
        $this->assertFalse(
            (bool) preg_match('/<aside[^>]+x-show="sidebarOpen"/', $content)
        );
    }

    public function test_admin_has_persistent_admin_sidebar(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('<aside', $content);
        $this->assertStringContainsString('Super Admin', $content);
        $this->assertFalse(
            (bool) preg_match('/<aside[^>]+x-show="sidebarOpen"/', $content)
        );
    }

    public function test_domestic_admin_has_persistent_domestic_sidebar(): void
    {
        $domAdmin = User::factory()->create([
            'role' => 'domestic_admin',
            'user_type' => 'domestic_admin',
        ]);

        $response = $this->actingAs($domAdmin)->get(route('domestic.dashboard'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('<aside', $content);
        $this->assertStringContainsString('Domestic Admin', $content);
        $this->assertFalse(
            (bool) preg_match('/<aside[^>]+x-show="sidebarOpen"/', $content)
        );
    }

    public function test_international_admin_has_persistent_international_sidebar(): void
    {
        $intlAdmin = User::factory()->create([
            'role' => 'international_admin',
            'user_type' => 'international_admin',
        ]);

        $response = $this->actingAs($intlAdmin)->get(route('international.dashboard'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('<aside', $content);
        $this->assertStringContainsString('International Air Cargo', $content);
        $this->assertFalse(
            (bool) preg_match('/<aside[^>]+x-show="sidebarOpen"/', $content)
        );
    }

    public function test_seller_has_persistent_seller_sidebar(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'user_type' => 'seller',
        ]);

        $response = $this->actingAs($seller)->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('<aside', $content);
        $this->assertStringContainsString('Seller', $content);
        $this->assertFalse(
            (bool) preg_match('/<aside[^>]+x-show="sidebarOpen"/', $content)
        );
    }

    public function test_rider_has_persistent_rider_sidebar(): void
    {
        $rider = User::factory()->create([
            'role' => 'rider',
            'user_type' => 'rider',
        ]);

        $response = $this->actingAs($rider)->get(route('rider.dashboard'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('<aside', $content);
        $this->assertStringContainsString('Rider', $content);
        $this->assertFalse(
            (bool) preg_match('/<aside[^>]+x-show="sidebarOpen"/', $content)
        );
    }

    public function test_partner_has_persistent_partner_sidebar(): void
    {
        $partner = User::factory()->create([
            'role' => 'partner',
            'user_type' => 'partner',
        ]);

        $response = $this->actingAs($partner)->get(route('partner.dashboard'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('<aside', $content);
        $this->assertStringContainsString('Partner', $content);
        $this->assertFalse(
            (bool) preg_match('/<aside[^>]+x-show="sidebarOpen"/', $content)
        );
    }

    public function test_mobile_simulator_renders_successfully(): void
    {
        $response = $this->get('/simulator');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('App Simulator & Studio', $content);
        $this->assertStringContainsString('iPhone 16 Pro', $content);
        $this->assertStringContainsString('Galaxy S24 Ultra', $content);
        $this->assertStringContainsString('Pixel 9 Pro', $content);
        $this->assertStringContainsString('id="simulatorFrame"', $content);
    }

    public function test_sidebar_has_non_distorting_responsive_toggle_classes(): void
    {
        $response = $this->get('/rates/inquiry');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Check for non-distorting slide transition classes
        $this->assertStringContainsString('lg:-ml-64', $content);
        $this->assertStringContainsString('-translate-x-full', $content);
        $this->assertStringContainsString('toggleSidebar()', $content);
        $this->assertStringContainsString('window.innerWidth >= 1024', $content);
    }
}

