<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAndDashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_redirects_to_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'super@netpack.test',
            'password' => bcrypt('password123'),
            'user_type' => 'super_admin',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'super@netpack.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->actingAs($user)->get('/admin/dashboard')->assertOk();
    }

    public function test_domestic_admin_redirects_to_domestic_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'domestic@netpack.test',
            'password' => bcrypt('password123'),
            'user_type' => 'domestic_admin',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'domestic@netpack.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/domestic/dashboard');
        $this->actingAs($user)->get('/domestic/dashboard')->assertOk();
    }

    public function test_staff_user_redirects_to_domestic_dashboard_without_403(): void
    {
        $user = User::factory()->create([
            'email' => 'staff@netpack.test',
            'password' => bcrypt('password123'),
            'user_type' => 'staff',
            'service_scope' => 'domestic',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'staff@netpack.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/domestic/dashboard');
        $this->actingAs($user)->get('/domestic/dashboard')->assertOk();
    }

    public function test_international_admin_redirects_to_international_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'intl@netpack.test',
            'password' => bcrypt('password123'),
            'user_type' => 'international_admin',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'intl@netpack.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/international/dashboard');
        $this->actingAs($user)->get('/international/dashboard')->assertOk();
    }

    public function test_client_and_customer_redirect_to_client_dashboard(): void
    {
        $client = User::factory()->create([
            'email' => 'client@netpack.test',
            'password' => bcrypt('password123'),
            'user_type' => 'client',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'client@netpack.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/client/dashboard');
        $this->actingAs($client)->get('/client/dashboard')->assertOk();

        $customer = User::factory()->create([
            'email' => 'cust@netpack.test',
            'password' => bcrypt('password123'),
            'user_type' => 'customer',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $responseCust = $this->post('/login', [
            'email' => 'cust@netpack.test',
            'password' => 'password123',
        ]);

        $responseCust->assertRedirect('/client/dashboard');
        $this->actingAs($customer)->get('/client/dashboard')->assertOk();
    }

    public function test_cross_portal_intended_session_url_is_sanitized_preventing_403(): void
    {
        $domesticAdmin = User::factory()->create([
            'email' => 'domadmin@netpack.test',
            'password' => bcrypt('password123'),
            'user_type' => 'domestic_admin',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        // Simulate session having url.intended = /client/dashboard from a previous guest attempt
        $response = $this->withSession(['url.intended' => 'http://localhost/client/dashboard'])
            ->post('/login', [
                'email' => 'domadmin@netpack.test',
                'password' => 'password123',
            ]);

        // Must NOT redirect to /client/dashboard (which would cause 403)
        $response->assertRedirect('/domestic/dashboard');
        $this->assertNotSame('/client/dashboard', $response->headers->get('Location'));
    }

    public function test_home_controller_redirects_each_role_to_proper_dashboard(): void
    {
        $domStaff = User::factory()->create(['user_type' => 'staff', 'service_scope' => 'domestic', 'verification_status' => 'approved']);
        $this->actingAs($domStaff)->get('/dashboard')->assertRedirect(route('domestic.dashboard'));

        $intlStaff = User::factory()->create(['user_type' => 'staff', 'service_scope' => 'international', 'verification_status' => 'approved']);
        $this->actingAs($intlStaff)->get('/dashboard')->assertRedirect(route('international.dashboard'));

        $superStaff = User::factory()->create(['user_type' => 'staff', 'service_scope' => 'all', 'verification_status' => 'approved']);
        $this->actingAs($superStaff)->get('/dashboard')->assertRedirect(route('admin.dashboard'));

        $seller = User::factory()->create(['user_type' => 'seller', 'verification_status' => 'approved']);
        $this->actingAs($seller)->get('/dashboard')->assertRedirect(route('seller.dashboard'));

        $rider = User::factory()->create(['user_type' => 'rider', 'verification_status' => 'approved']);
        $this->actingAs($rider)->get('/dashboard')->assertRedirect(route('rider.dashboard'));

        $client = User::factory()->create(['user_type' => 'client', 'verification_status' => 'approved']);
        $this->actingAs($client)->get('/dashboard')->assertRedirect(route('client.dashboard'));
    }

    public function test_seller_redirects_to_seller_dashboard_with_autofill_credentials(): void
    {
        $seller = User::factory()->create([
            'email' => 'seller@netpack.test',
            'password' => bcrypt('Netpack!Seller#2026'),
            'user_type' => 'seller',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'seller@netpack.test',
            'password' => 'Netpack!Seller#2026',
        ]);

        $response->assertRedirect('/seller/dashboard');
        $this->actingAs($seller)->get('/seller/dashboard')->assertOk();
    }

    public function test_rider_redirects_to_rider_dashboard_with_autofill_credentials(): void
    {
        $rider = User::factory()->create([
            'email' => 'rider@netpack.test',
            'password' => bcrypt('Netpack!Rider#2026'),
            'user_type' => 'rider',
            'verification_status' => 'approved',
            'password_changed' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'rider@netpack.test',
            'password' => 'Netpack!Rider#2026',
        ]);

        $response->assertRedirect('/rider/dashboard');
        $this->actingAs($rider)->get('/rider/dashboard')->assertOk();
    }

    public function test_demo_seller_and_rider_fallback_passwords_authenticate_successfully(): void
    {
        // Even if seeded with password123, logging in with Netpack!Seller#2026 or password123 succeeds
        $seller = User::factory()->create([
            'email' => 'seller@test.com',
            'password' => bcrypt('password123'),
            'user_type' => 'seller',
            'verification_status' => 'approved',
            'password_changed' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'seller@test.com',
            'password' => 'Netpack!Seller#2026',
        ]);

        $response->assertRedirect('/seller/dashboard');
        $this->assertTrue($seller->fresh()->password_changed);
    }
}
