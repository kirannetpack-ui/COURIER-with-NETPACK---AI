<?php

namespace Tests\Feature;

use App\Models\HsCode;
use App\Models\User;
use Database\Seeders\HsCodeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HsCodeTariffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HsCodeSeeder::class);
    }

    public function test_hs_codes_are_properly_seeded_with_nepal_and_wco_tariffs(): void
    {
        $this->assertDatabaseHas('hs_codes', [
            'code' => '6214.20.00',
            'commodity_name' => 'Pashmina & Cashmere Shawls, Scarves & Stoles',
        ]);

        $this->assertDatabaseHas('hs_codes', [
            'code' => '0902.10.00',
            'commodity_name' => 'Himalayan Orthodox Black Tea',
        ]);

        $this->assertDatabaseHas('hs_codes', [
            'code' => '9206.00.00',
            'commodity_name' => 'Hand-Hammered Tibetan Singing Bowls & Gongs',
        ]);

        $count = HsCode::count();
        $this->assertGreaterThanOrEqual(40, $count);
    }

    public function test_api_can_search_hs_codes_by_commodity_keyword(): void
    {
        $response = $this->getJson('/api/hs-codes/search?q=pashmina');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'count',
                'query',
                'results',
                'data',
            ])
            ->assertJsonFragment([
                'code' => '6214.20.00',
            ]);

        $this->assertTrue(collect($response->json('data'))->contains(function ($item) {
            return str_contains(strtolower($item['commodity_name']), 'pashmina');
        }));
    }

    public function test_api_can_search_hs_codes_by_code_prefix(): void
    {
        $response = $this->getJson('/api/hs-codes/search?q=0902');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertStringStartsWith('0902', $data[0]['code']);
    }

    public function test_api_can_filter_hs_codes_by_category(): void
    {
        $response = $this->getJson('/api/hs-codes/search?category=' . urlencode('Textiles & Pashmina'));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertEquals('Textiles & Pashmina', $item['category']);
        }
    }

    public function test_api_returns_distinct_hs_categories(): void
    {
        $response = $this->getJson('/api/hs-codes/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'categories',
            ]);

        $categories = $response->json('categories');
        $this->assertContains('Textiles & Pashmina', $categories);
        $this->assertContains('Handicrafts & Art', $categories);
        $this->assertContains('Tea, Coffee & Agro', $categories);
    }
}
