<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AiAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_assistant_page_renders_successfully(): void
    {
        $user = User::factory()->create([
            'name' => 'Kiran Sharma',
            'user_type' => 'client',
        ]);

        $response = $this->actingAs($user)->get('/ai-assistant');

        $response->assertStatus(200);
        $response->assertSee('NETPACK AI Copilot');
        $response->assertSee('Important Occasions, Festivals', false);
        $response->assertSee('Dual-OTP Cryptographic Custody Transfer', false);
    }

    public function test_ai_greeting_endpoint_returns_dynamic_personalized_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Aarav Adhikari',
            'user_type' => 'client',
        ]);

        $response = $this->actingAs($user)->getJson('/ai/greeting');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'greeting',
                'client_name',
                'role_title',
                'gesture',
                'time_of_day',
                'nepal_time',
                'quick_suggestions',
            ],
        ]);

        $data = $response->json('data');
        $this->assertStringContainsString('Aarav Ji', $data['client_name']);
        $this->assertNotEmpty($data['quick_suggestions']);
    }

    public function test_ai_chat_provides_door_to_door_delivery_otp_guidance(): void
    {
        $user = User::factory()->create(['name' => 'Suman Thapa']);

        $response = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'Please explain door to door delivery and how the secret OTP handover works.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $content = $response->json('response');
        $this->assertStringContainsString('Pickup OTP', $content);
        $this->assertStringContainsString('Delivery OTP', $content);
        $this->assertStringContainsString('Proof of Delivery', $content);

        // Check speech_text is also returned for voice synthesis
        $speech = $response->json('speech_text');
        $this->assertNotEmpty($speech);
    }

    public function test_ai_chat_provides_cod_limit_guidance(): void
    {
        $user = User::factory()->create(['name' => 'Bikash KC', 'user_type' => 'rider']);

        $response = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'What are the Cash on Delivery COD limits and ledger rules?',
        ]);

        $response->assertStatus(200);
        $content = $response->json('response');
        $this->assertStringContainsString('Tiered Cash On Delivery', $content);
        $this->assertStringContainsString('Level 1', $content);
        $this->assertStringContainsString('Level 2', $content);
        $this->assertStringContainsString('Level 3', $content);
        $this->assertStringContainsString('Segregation', $content);
    }

    public function test_ai_chat_provides_occasions_and_festival_schedules(): void
    {
        $user = User::factory()->create(['name' => 'Meera Shrestha']);

        $response = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'What are the Dashain and Tihar festival delivery cutoffs and schedules?',
        ]);

        $response->assertStatus(200);
        $content = $response->json('response');
        $this->assertStringContainsString('Bada Dashain', $content);
        $this->assertStringContainsString('Same-Day Intra-City Delivery Cutoff', $content);
        $this->assertStringContainsString('TIA Cargo Terminal', $content);
    }

    public function test_ai_chat_quick_tariff_estimate(): void
    {
        $user = User::factory()->create(['name' => 'Client Test']);

        $response = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'What is the rate from Kathmandu to Pokhara for 2 kg parcel?',
        ]);

        $response->assertStatus(200);
        $content = $response->json('response');
        $this->assertStringContainsString('Instant Domestic Tariff Estimate', $content);
        $this->assertStringContainsString('Kathmandu', $content);
        $this->assertStringContainsString('Pokhara', $content);
        $this->assertStringContainsString('Rs.', $content);
    }

    public function test_ai_occasions_endpoint_returns_calendar_and_schedules(): void
    {
        $response = $this->getJson('/ai/occasions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'occasions',
            'schedules' => [
                'same_day_cutoff',
                'express_intacity_hours',
                'tia_cargo_intake_cutoff',
                'night_linehaul_departure',
            ],
            'current_occasion',
        ]);

        $occasions = $response->json('occasions');
        $this->assertNotEmpty($occasions);
        $ids = array_column($occasions, 'id');
        $this->assertContains('dashain', $ids);
        $this->assertContains('tihar', $ids);
    }

    public function test_ai_speech_sanitization_removes_markdown(): void
    {
        $service = new AiAssistantService();
        $markdown = "### 🚪 Door-to-Door Delivery\n* **Step 1**: Use `Pickup OTP` to verify.\n[Click here](/tracking)";
        $clean = $service->sanitizeForSpeech($markdown);

        $this->assertStringNotContainsString('###', $clean);
        $this->assertStringNotContainsString('**', $clean);
        $this->assertStringNotContainsString('`', $clean);
        $this->assertStringNotContainsString('[Click here]', $clean);
        $this->assertStringContainsString('Step 1: Use Pickup OTP to verify', $clean);
    }
}
