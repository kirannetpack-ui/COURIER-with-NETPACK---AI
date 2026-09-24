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

    public function test_ai_chat_parses_user_name_and_tailored_jhapa_to_poland_shipment_process(): void
    {
        $user = User::factory()->create(['name' => 'Customer']);

        $query = 'HelloMy name is Kiran. you can call me with that name. I want to book an 20kg shipment for Poland which is to be picked up from Jhapa. Could you please assist me with theprocess to do so. Also show me how the whole process works.';

        $response = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => $query,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'client_name' => 'Kiran Ji',
        ]);

        $content = $response->json('response');

        // Verify personalization
        $this->assertStringContainsString('Kiran Ji', $content);

        // Verify logistics entity parsing
        $this->assertStringContainsString('Jhapa', $content);
        $this->assertStringContainsString('Poland', $content);
        $this->assertStringContainsString('20', $content);

        // Verify exact 4-stage operational blueprint
        $this->assertStringContainsString('Stage 1', $content);
        $this->assertStringContainsString('Feeder Linehaul', $content);
        $this->assertStringContainsString('Pickup OTP', $content);
        $this->assertStringContainsString('Biratnagar Central Hub', $content);

        $this->assertStringContainsString('Stage 2', $content);
        $this->assertStringContainsString('Tribhuvan International Airport', $content);
        $this->assertStringContainsString('House Air Waybill', $content);
        $this->assertStringContainsString('Zero-Charges', $content);

        $this->assertStringContainsString('Stage 3', $content);
        $this->assertStringContainsString('Warsaw Chopin Airport', $content);

        $this->assertStringContainsString('Stage 4', $content);
        $this->assertStringContainsString('DPD Poland / DHL Express', $content);

        // Verify interactive booking action button
        $actions = $response->json('actions');
        $this->assertNotEmpty($actions);
        $bookingAction = $actions[0];
        $this->assertStringContainsString('Book 20kg Jhapa to Poland', $bookingAction['label']);
        $this->assertStringContainsString('/shipments/create?', $bookingAction['url']);
        $this->assertStringContainsString('receiver_country=Poland', $bookingAction['url']);
        $this->assertStringContainsString('pickup_city=Jhapa', $bookingAction['url']);
        $this->assertStringContainsString('pickup_location_type=outside_ktm', $bookingAction['url']);
    }

    public function test_ai_chat_retains_preferred_name_in_session_across_turns(): void
    {
        $user = User::factory()->create(['name' => 'Default Name']);

        // First message introducing name
        $res1 = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'My name is Kiran. I want to know about shipping.',
        ]);
        $res1->assertStatus(200);
        $this->assertEquals('Kiran Ji', $res1->json('client_name'));

        // Subsequent message without mentioning name
        $res2 = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'Explain the door to door delivery OTP security.',
        ]);
        $res2->assertStatus(200);
        $this->assertEquals('Kiran Ji', $res2->json('client_name'));
        $this->assertStringContainsString('Kiran Ji', $res2->json('response'));
    }

    public function test_voice_autofill_parse_endpoint_normalizes_spoken_form_inputs(): void
    {
        $user = User::factory()->create();

        // 1. Spoken mode
        $res1 = $this->actingAs($user)->postJson('/ai/voice-autofill-parse', [
            'step' => 'mode',
            'spoken_text' => 'I want to book an international air cargo shipment to Poland',
        ]);
        $res1->assertStatus(200);
        $this->assertEquals('international', $res1->json('data.parsed_value'));

        // 2. Spoken city
        $res2 = $this->actingAs($user)->postJson('/ai/voice-autofill-parse', [
            'step' => 'pickup_city',
            'spoken_text' => 'Please pick it up from Damak Jhapa',
        ]);
        $res2->assertStatus(200);
        $this->assertEquals('Jhapa', $res2->json('data.parsed_value'));

        // 3. Spoken phone number with words
        $res3 = $this->actingAs($user)->postJson('/ai/voice-autofill-parse', [
            'step' => 'sender_phone',
            'spoken_text' => 'phone number is nine eight four one two three four five six seven',
        ]);
        $res3->assertStatus(200);
        $this->assertEquals('9841234567', $res3->json('data.parsed_value'));

        // 4. Spoken country
        $res4 = $this->actingAs($user)->postJson('/ai/voice-autofill-parse', [
            'step' => 'destination',
            'spoken_text' => 'shipping to Poland in Europe',
            'mode' => 'international',
        ]);
        $res4->assertStatus(200);
        $this->assertEquals('Poland', $res4->json('data.parsed_value'));

        // 5. Spoken weight
        $res5 = $this->actingAs($user)->postJson('/ai/voice-autofill-parse', [
            'step' => 'weight',
            'spoken_text' => 'consignment weight is 20 kg',
        ]);
        $res5->assertStatus(200);
        $this->assertEquals(20.0, $res5->json('data.parsed_value'));
    }

    public function test_shipment_create_renders_voice_autofill_invitation_beforehand(): void
    {
        $user = User::factory()->create(['name' => 'Kiran Sharma']);

        $response = $this->actingAs($user)->get('/shipments/create');

        $response->assertStatus(200);
        $response->assertSee('ai-voice-invitation-banner');
        $response->assertSee('Would you like AI Voice Autofill Assistance?');
        $response->assertSee('Yes, Guide Me by Voice');
        $response->assertSee('No, I\'ll Type Manually', false);
        $response->assertSee('voice-autofill-header-btn');
        $response->assertSee('Nepalese Accent Calibrated');
    }

    public function test_ai_understands_nepglish_and_romanized_nepali_keywords(): void
    {
        $user = User::factory()->create(['name' => 'Kiran']);

        // Query with Nepglish/Nepali terms: "bata", "pathaune", "bis kg", "kati lagchha"
        $response = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'Mero naam Kiran ho, Jhapa bata Poland pathaune bis kg luga cha, kati lagchha ra process k ho?',
        ]);

        $response->assertStatus(200);
        $content = $response->json('response');

        // Verify entity recognition from Romanized Nepali
        $this->assertStringContainsString('Jhapa', $content);
        $this->assertStringContainsString('Poland', $content);
        $this->assertStringContainsString('20 kg', $content);
        $this->assertStringContainsString('Consignment Roadmap', $content);
        $this->assertStringContainsString('Kiran Ji', $response->json('client_name'));

        // Check speech contains respectful Nepalese greeting and rate/cadence
        $speech = $response->json('speech_text');
        $this->assertStringContainsString('Namaste Kiran Ji', $speech);
    }

    public function test_voice_autofill_parses_nepali_numbers_and_local_commodities(): void
    {
        $user = User::factory()->create();

        // 1. Spoken weight with Romanized Nepali word "pachis" (25)
        $resWeight = $this->actingAs($user)->postJson('/ai/voice-autofill-parse', [
            'step' => 'weight',
            'spoken_text' => 'pachis kilo ko cha',
        ]);
        $resWeight->assertStatus(200);
        $this->assertEquals(25.0, $resWeight->json('data.parsed_value'));

        // 2. Spoken phone with Romanized Nepali numbers
        $resPhone = $this->actingAs($user)->postJson('/ai/voice-autofill-parse', [
            'step' => 'sender_phone',
            'spoken_text' => 'mero phone number nau aath char ek dui tin char panch chha sat ho',
        ]);
        $resPhone->assertStatus(200);
        $this->assertEquals('9841234567', $resPhone->json('data.parsed_value'));

        // 3. Spoken commodity "luga" -> "Apparel & Garments"
        $resDesc = $this->actingAs($user)->postJson('/ai/voice-autofill-parse', [
            'step' => 'description',
            'spoken_text' => 'bhari ma sabai luga cha',
        ]);
        $resDesc->assertStatus(200);
        $this->assertEquals('Apparel & Garments', $resDesc->json('data.parsed_value'));

        // 4. Spoken destination with suffix "Poland pathaune"
        $resDest = $this->actingAs($user)->postJson('/ai/voice-autofill-parse', [
            'step' => 'destination',
            'spoken_text' => 'Poland pathaune ho',
            'mode' => 'international',
        ]);
        $resDest->assertStatus(200);
        $this->assertEquals('Poland', $resDest->json('data.parsed_value'));
    }

    public function test_admin_operational_intelligence_endpoint_and_win_win_win_generation(): void
    {
        $admin = User::factory()->create([
            'name' => 'General Manager',
            'user_type' => 'super_admin',
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/ai/operational-intelligence');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'issues',
            'critical_count',
            'warning_count',
            'report' => [
                'speech_text',
                'response',
            ],
        ]);

        $issues = $response->json('issues');
        $this->assertIsArray($issues);
        $this->assertNotEmpty($issues);

        // Verify each issue contains the Tripartite Win-Win-Win structure
        foreach ($issues as $iss) {
            $this->assertArrayHasKey('win_win_win', $iss);
            $this->assertArrayHasKey('client', $iss['win_win_win']);
            $this->assertArrayHasKey('operations', $iss['win_win_win']);
            $this->assertArrayHasKey('company', $iss['win_win_win']);
            $this->assertNotEmpty($iss['win_win_win']['client']);
            $this->assertNotEmpty($iss['win_win_win']['operations']);
            $this->assertNotEmpty($iss['win_win_win']['company']);
        }
    }

    public function test_admin_can_execute_win_win_resolution_action(): void
    {
        $admin = User::factory()->create([
            'name' => 'Operations Director',
            'user_type' => 'super_admin',
        ]);

        // Create a shipment marked as delayed
        $shipment = \App\Models\Shipment::create([
            'customer_id' => $admin->id,
            'tracking_number' => 'NP-TEST-DELAY-01',
            'sender_name' => 'Kiran Sender',
            'receiver_name' => 'Client Receiver',
            'sender_phone' => '9841000000',
            'receiver_phone' => '9842000000',
            'origin_city' => 'Jhapa',
            'destination_city' => 'Kathmandu',
            'status' => 'in_transit',
            'is_delayed' => true,
            'shipment_type' => 'domestic',
            'receiver_country' => 'Nepal',
            'weight' => 2.0,
        ]);



        $response = $this->actingAs($admin)->postJson('/admin/ai/resolve-issue-action', [
            'issue_id' => 'delay-' . $shipment->id,
            'action_type' => 'notify_and_reroute',
            'shipment_id' => $shipment->id,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        // Verify shipment status was updated
        $freshShipment = $shipment->fresh();
        $this->assertFalse((bool) $freshShipment->is_delayed);
        $this->assertStringContainsString('AI Reassurance triggered', $freshShipment->status_notes);
    }

    public function test_admin_dashboard_renders_ai_operational_intelligence_hub(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'user_type' => 'super_admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('AI Operational Intelligence', false);
        $response->assertSee('Win-Win-Win Hub', false);
        $response->assertSee('Nepalese Orientation', false);
        $response->assertSee('Voice Briefing (Nepali Cadence)', false);
    }

    public function test_voice_autofill_parses_all_three_services_including_ecommerce(): void
    {
        $service = app(AiAssistantService::class);

        // 1. E-Commerce & COD service recognition
        $ecomResult = $service->parseVoiceFormField('mode', 'I want e-commerce delivery with cash on delivery');
        $this->assertEquals('ecommerce', $ecomResult['parsed_value']);
        $this->assertStringContainsString('E-Commerce and Cash on Delivery', $ecomResult['speech_ack']);

        $ecomResult2 = $service->parseVoiceFormField('mode', 'service 3 online store rider dispatch');
        $this->assertEquals('ecommerce', $ecomResult2['parsed_value']);

        // 2. International Air Cargo service recognition
        $intlResult = $service->parseVoiceFormField('mode', 'Send international air cargo overseas to Poland');
        $this->assertEquals('international', $intlResult['parsed_value']);
        $this->assertStringContainsString('International Air Cargo', $intlResult['speech_ack']);

        // 3. Domestic Express service recognition
        $domResult = $service->parseVoiceFormField('mode', 'Domestic delivery in Nepal');
        $this->assertEquals('domestic', $domResult['parsed_value']);
        $this->assertStringContainsString('Domestic Express', $domResult['speech_ack']);
    }

    public function test_ai_identifies_all_three_services_overview_and_ecommerce_guidance(): void
    {
        $user = User::factory()->create(['name' => 'Kiran User']);

        // Test asking for all 3 services
        $response = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'What are the 3 services that NETPACK provides?',
        ]);

        $response->assertStatus(200);
        $content = $response->json('response');
        $this->assertStringContainsString('Domestic Express Delivery', $content);
        $this->assertStringContainsString('International Air Cargo', $content);
        $this->assertStringContainsString('E-Commerce & Cash on Delivery', $content);

        // Verify actions contain direct links to all 3 modes
        $actions = $response->json('actions');
        $this->assertCount(3, $actions);
        $urls = array_column($actions, 'url');
        $this->assertContains('/shipments/create?mode=domestic', $urls);
        $this->assertContains('/shipments/create?mode=international', $urls);
        $this->assertContains('/shipments/create?mode=ecommerce', $urls);

        // Test dedicated E-Commerce inquiry
        $ecomResponse = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'How does the ecommerce delivery and COD payout work for online sellers?',
        ]);

        $ecomResponse->assertStatus(200);
        $ecomContent = $ecomResponse->json('response');
        $this->assertStringContainsString('E-Commerce Express', $ecomContent);
        $this->assertStringContainsString('Pickup OTP', $ecomContent);
        $this->assertStringContainsString('Delivery OTP', $ecomContent);
        $this->assertStringContainsString('Bank Account, eSewa, or Khalti', $ecomContent);
    }

    public function test_ai_parses_ecommerce_logistics_intent(): void
    {
        $service = app(AiAssistantService::class);
        $entities = $service->parseLogisticsEntities('I run an online store and need COD pickup for my customer');

        $this->assertTrue($entities['is_ecommerce']);
    }
}


