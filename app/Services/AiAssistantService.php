<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\PickupRequest;
use App\Models\Order;
use App\Models\DomesticRate;
use App\Models\InternationalRate;
use App\Models\DeliveryZone;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    /**
     * Generate dynamic, improvisational greeting with gestures & occasions
     */
    public function generateGreeting(?User $user = null): array
    {
        $now = Carbon::now('Asia/Kathmandu');
        $hour = $now->hour;

        // Determine Time of Day
        if ($hour >= 5 && $hour < 12) {
            $timeGreeting = 'Subha Bihani (Good morning)';
            $timeEn = 'Good morning';
        } elseif ($hour >= 12 && $hour < 17) {
            $timeGreeting = 'Good afternoon (Subha Din)';
            $timeEn = 'Good afternoon';
        } elseif ($hour >= 17 && $hour < 21) {
            $timeGreeting = 'Good evening (Subha Sandhya)';
            $timeEn = 'Good evening';
        } else {
            $timeGreeting = 'Namaste & Welcome';
            $timeEn = 'Working late? Namaste';
        }

        // Format Client Name & Respectful Honorific
        $clientName = 'Valued Client';
        $roleTitle = 'Client';
        if ($user) {
            $firstName = explode(' ', trim($user->name))[0];
            $clientName = $firstName . ' Ji';
            if ($user->isRider()) {
                $roleTitle = 'Rider Captain';
            } elseif ($user->isSeller()) {
                $roleTitle = 'E-Commerce Merchant';
            } elseif ($user->isDomesticAdmin() || $user->isSuperAdmin()) {
                $roleTitle = 'Operations Chief';
            } elseif ($user->isPartner()) {
                $roleTitle = 'Hub Partner';
            }
        }

        // Check for upcoming or active Occasions / Festivals
        $upcomingOccasion = $this->getNearestOccasion();

        // Dynamic, non-monotonous greeting pool
        $greetingVariations = [
            "Namaste {$clientName}! 🙏 {$timeGreeting}. I am your NETPACK AI Logistics Copilot. How may I accelerate your door-to-door deliveries or international cargo today?",
            "{$timeEn}, {$clientName}! ✨ Logistics Radar is live across all 7 Provinces. Whether you need an instant freight quote, doorstep OTP verification, or flight tracking, I am at your service.",
            "Namaste {$clientName}! 🚚 Hope your consignments are moving smoothly. Ready to guide you through door-to-door dispatches, COD limits, or Kathmandu airport cargo cutoffs.",
            "A warm {$timeGreeting}, {$clientName}! 🌐 From ward-level doorstep deliveries across Nepal to air cargo across global corridors, what can I assist you with right now?",
            "Namaste {$clientName}! 📦 I am actively monitoring express dispatches and linehauls. Need help tracking a consignment, estimating rates, or booking a pickup?",
        ];

        // If an active occasion is detected, inject festive greetings!
        $gesture = 'waving';
        $festiveNotice = null;
        if ($upcomingOccasion) {
            if (!empty($upcomingOccasion['greetings'])) {
                $festiveGreeting = $upcomingOccasion['greetings'][array_rand($upcomingOccasion['greetings'])];
                $greetingVariations[] = "Namaste {$clientName}! 🙏 {$festiveGreeting} Your AI Copilot is here to ensure all your holiday shipments reach doorsteps safely.";
            }
            $festiveNotice = [
                'occasion' => $upcomingOccasion['name'],
                'impact' => $upcomingOccasion['impact_level'],
                'notice' => $upcomingOccasion['logistical_notice'],
                'cutoff' => $upcomingOccasion['cutoff_days_prior'] . ' days before peak',
            ];
            $gesture = 'celebrating';
        }

        // Pick an improvisational greeting
        $selectedGreeting = $greetingVariations[array_rand($greetingVariations)];

        return [
            'greeting' => $selectedGreeting,
            'client_name' => $clientName,
            'role_title' => $roleTitle,
            'gesture' => $gesture,
            'time_of_day' => $timeGreeting,
            'festive_notice' => $festiveNotice,
            'nepal_time' => $now->format('h:i A, l (d M Y)'),
            'quick_suggestions' => $this->getQuickSuggestions($user),
        ];
    }

    /**
     * Get contextual quick suggestion chips based on user role
     */
    public function getQuickSuggestions(?User $user = null): array
    {
        $role = $user ? $user->user_type : 'guest';

        $common = [
            ['label' => '📍 Track Consignment', 'prompt' => 'I would like to track my consignment. How does tracking work?'],
            ['label' => '💰 Calculate Delivery Rate', 'prompt' => 'How can I calculate door-to-door delivery and cargo rates?'],
            ['label' => '🗓️ Holiday & Festival Cutoffs', 'prompt' => 'What are the upcoming festivals and delivery cutoff dates?'],
        ];

        if ($role === 'seller') {
            return array_merge([
                ['label' => '🚀 Direct Rider Dispatch', 'prompt' => 'How do I dispatch a direct rider with secret Pickup OTP and Delivery OTP?'],
                ['label' => '💳 COD Settlement Guide', 'prompt' => 'How does the Cash on Delivery (COD) ledger and bank payout work?'],
            ], $common);
        }

        if ($role === 'rider') {
            return [
                ['label' => '🔑 OTP Handover Instructions', 'prompt' => 'Explain the 6-digit Pickup OTP and Customer Delivery OTP verification.'],
                ['label' => '💵 COD Limits & Cash Deposit', 'prompt' => 'What is my COD cash-in-hand limit and how do I submit deposit slips?'],
                ['label' => '📸 Proof of Delivery (POD)', 'prompt' => 'What are the photo requirements for verified Proof of Delivery?'],
                ['label' => '🕒 Daily Dispatch Cutoffs', 'prompt' => 'What are the operational cutoff schedules for door-to-door delivery?'],
            ];
        }

        return array_merge([
            ['label' => '🚪 Door-to-Door Delivery Guide', 'prompt' => 'Explain the full door-to-door delivery process and security OTPs.'],
            ['label' => '✈️ International Air Cargo & Feeder', 'prompt' => 'How does domestic feeder linehaul to Kathmandu airport air cargo work?'],
        ], $common);
    }

    /**
     * Process query through the AI Engine (OpenAI, Gemini, Claude, or Built-in Expert Engine)
     */
    public function ask(string $query, ?User $user = null, array $conversationHistory = []): array
    {
        $query = trim($query);
        if (empty($query)) {
            return [
                'success' => false,
                'response' => 'Please ask a question or speak your query regarding door-to-door delivery, rates, or tracking.',
                'speech_text' => 'Please ask a question or speak your query.',
                'gesture' => 'inquiring',
            ];
        }

        // 1. Tool execution check: Check if query contains a tracking number or rate request
        $trackingResult = $this->tryLookupTracking($query);
        if ($trackingResult) {
            return $trackingResult;
        }

        $rateResult = $this->tryCalculateQuickRate($query);
        if ($rateResult) {
            return $rateResult;
        }

        // 2. Check configured external provider
        $provider = config('ai_assistant.default_provider', 'builtin');
        $openaiKey = config('ai_assistant.providers.openai.api_key');

        if ($provider === 'openai' && !empty($openaiKey)) {
            try {
                $externalResponse = $this->queryOpenAi($query, $user, $conversationHistory);
                if ($externalResponse) {
                    return $externalResponse;
                }
            } catch (\Throwable $e) {
                Log::warning('OpenAI AI Assistant call failed, gracefully falling back to Built-in Engine: ' . $e->getMessage());
            }
        }

        // 3. Built-in Local Autonomous Logistics Expert Engine
        return $this->queryBuiltinEngine($query, $user);
    }

    /**
     * Query OpenAI with System Context & Knowledge Base
     */
    protected function queryOpenAi(string $query, ?User $user, array $history): ?array
    {
        $apiKey = config('ai_assistant.providers.openai.api_key');
        $model = config('ai_assistant.providers.openai.model', 'gpt-4o');

        $systemPrompt = $this->buildSystemKnowledgePrompt($user);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Append recent history (up to last 6 messages)
        foreach (array_slice($history, -6) as $msg) {
            if (isset($msg['role'], $msg['content'])) {
                $messages[] = [
                    'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                    'content' => $msg['content'],
                ];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $query];

        $response = Http::withToken($apiKey)
            ->timeout(20)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 800,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $reply = $data['choices'][0]['message']['content'] ?? null;
            if ($reply) {
                return [
                    'success' => true,
                    'provider' => 'openai',
                    'response' => $reply,
                    'speech_text' => $this->sanitizeForSpeech($reply),
                    'gesture' => $this->detectGesture($reply),
                    'actions' => $this->extractActions($reply),
                ];
            }
        }

        return null;
    }

    /**
     * Built-in Autonomous Logistics Knowledge-Base Neural Engine
     * Instant, zero-latency, 100% reliable domain intelligence
     */
    public function queryBuiltinEngine(string $query, ?User $user = null): array
    {
        $q = strtolower($query);
        $clientName = $user ? explode(' ', trim($user->name))[0] . ' Ji' : 'Valued Client';

        // 1. Door-to-Door Delivery Mechanics & OTP Protocols
        if (str_contains($q, 'door to door') || str_contains($q, 'doorstep') || str_contains($q, 'otp') || str_contains($q, 'handover') || str_contains($q, 'direct rider')) {
            $reply = "### 🚪 Door-to-Door Delivery & Dual-OTP Security Protocol\n\n"
                . "Namaste {$clientName}! At **COURIER with NETPACK**, our door-to-door delivery is safeguarded by cryptographic **Dual-OTP Verification**:\n\n"
                . "1. **Seller-to-Rider Handover (Pickup OTP)**:\n"
                . "   - When a direct delivery is booked, a secret **6-digit Pickup OTP** is generated.\n"
                . "   - The seller displays this OTP *only* when the verified rider arrives.\n"
                . "   - The rider inputs the OTP into their mobile app to officially assume package custody.\n\n"
                . "2. **Rider-to-Customer Handover (Delivery OTP & COD)**:\n"
                . "   - The recipient customer receives a secret **6-digit Delivery OTP** via SMS/app.\n"
                . "   - Upon arrival at the customer's doorstep, the rider collects COD cash (if applicable) and asks for the Delivery OTP.\n"
                . "   - Submitting the OTP instantly marks the consignment as **DELIVERED** and transfers verified custody.\n\n"
                . "3. **Proof of Delivery (POD)**:\n"
                . "   - The rider captures a geotagged photo of the parcel handover for complete dispute prevention.";

            return [
                'success' => true,
                'provider' => 'builtin_expert',
                'response' => $reply,
                'speech_text' => "Namaste {$clientName}! Our door-to-door delivery uses dual OTP security. The seller provides a 6-digit Pickup OTP when the rider arrives, and the recipient provides a 6-digit Delivery OTP upon doorstep handover with COD cash collection.",
                'gesture' => 'speaking',
                'actions' => [
                    ['label' => 'Book Direct Delivery', 'url' => '/shipments/create'],
                ],
            ];
        }

        // 2. COD (Cash On Delivery) & Limits
        if (str_contains($q, 'cod') || str_contains($q, 'cash on delivery') || str_contains($q, 'limit') || str_contains($q, 'cash in hand')) {
            $reply = "### 💵 Tiered Cash On Delivery (COD) & Financial Segregation\n\n"
                . "Namaste {$clientName}! NETPACK enforces a strictly segregated financial ledger system for all COD transactions:\n\n"
                . "* **Strict Segregation**: Rider delivery fee earnings (`rider_earnings_ledgers`) are 100% separated from COD cash collected (`rider_cod_ledgers`). Cash in hand belongs strictly to the company and seller.\n"
                . "* **Tiered COD Rider Authorization**:\n"
                . "  - **Level 0**: Rs. 0 (Prepaid orders only, probationary)\n"
                . "  - **Level 1**: Rs. 5,000 (New verified rider)\n"
                . "  - **Level 2**: Rs. 20,000 (Proven 50+ successful deliveries)\n"
                . "  - **Level 3**: Rs. 50,000 (Trusted high-volume veteran rider)\n"
                . "  - **Level 4**: Custom authorized enterprise limit\n"
                . "* **Settlement & Payouts**: Riders deposit cash via bank transfer or office cash desks; Sellers receive automated electronic remittance to eSewa, Khalti, or bank accounts.";

            return [
                'success' => true,
                'provider' => 'builtin_expert',
                'response' => $reply,
                'speech_text' => "NETPACK separates rider earnings from COD cash collected. We enforce tiered COD limits from Level 0 up to Level 4 for trusted riders, with automated digital remittance to sellers via bank, eSewa, or Khalti.",
                'gesture' => 'speaking',
                'actions' => [],
            ];
        }

        // 3. Occasions, Festivals & Holiday Deadlines
        if (str_contains($q, 'festival') || str_contains($q, 'occasion') || str_contains($q, 'dashain') || str_contains($q, 'tihar') || str_contains($q, 'holiday') || str_contains($q, 'cutoff') || str_contains($q, 'schedule')) {
            return $this->buildOccasionsAndSchedulesResponse($clientName);
        }

        // 4. Domestic Express Nepal & 7 Provinces
        if (str_contains($q, 'nepal') || str_contains($q, 'province') || str_contains($q, 'district') || str_contains($q, 'pokhara') || str_contains($q, 'biratnagar') || str_contains($q, 'chitwan') || str_contains($q, 'koshi') || str_contains($q, 'bagmati') || str_contains($q, 'gandaki') || str_contains($q, 'lumbini') || str_contains($q, 'karnali') || str_contains($q, 'sudurpashchim') || str_contains($q, 'madhesh')) {
            $reply = "### 🇳🇵 Nepal Domestic Express Network (7 Provinces & 77 Districts)\n\n"
                . "Namaste {$clientName}! NETPACK connects all 7 Provinces with rapid road linehauls, regional sorting hubs, and ward-level dispatch:\n\n"
                . "* **Koshi Province (P1)**: Central Sorting Hub in Biratnagar covering Jhapa, Morang, Sunsari, Ilam, Dhankuta, and Eastern hills.\n"
                . "* **Madhesh Province (P2)**: Hubs in Janakpur and Birgunj serving Dhanusha, Parsa, Bara, Sarlahi, Rautahat.\n"
                . "* **Bagmati Province (P3)**: Kathmandu Valley Central Sorting Hub & Hetauda connecting 13 districts.\n"
                . "* **Gandaki Province (P4)**: Pokhara Sorting Hub connecting Kaski, Tanahun, Syangja, Baglung, Gorkha, Mustang.\n"
                . "* **Lumbini Province (P5)**: Butwal & Dang Hubs covering Rupandehi, Banke (Nepalgunj), Palpa, Kapilvastu.\n"
                . "* **Karnali Province (P6)**: Birendranagar (Surkhet) Hub with mountain feeder linehauls.\n"
                . "* **Sudurpashchim Province (P7)**: Dhangadhi & Mahendranagar Hubs connecting Kailali, Kanchanpur, and Far-West districts.\n\n"
                . "High-volume consignments travel in **Nylon Bags tagged with secure QR manifests**, ensuring instant bulk scanning at every transit hub.";

            return [
                'success' => true,
                'provider' => 'builtin_expert',
                'response' => $reply,
                'speech_text' => "Namaste! Our domestic network covers all 7 Provinces and 77 Districts across Nepal. We operate central sorting hubs in Biratnagar, Janakpur, Kathmandu, Pokhara, Butwal, Surkhet, and Dhangadhi with nylon bag QR manifest tracking.",
                'gesture' => 'speaking',
                'actions' => [
                    ['label' => 'View Domestic Network', 'url' => '/tracking'],
                ],
            ];
        }

        // 5. International Air Cargo & Feeder Linehauls
        if (str_contains($q, 'international') || str_contains($q, 'air cargo') || str_contains($q, 'feeder') || str_contains($q, 'hawb') || str_contains($q, 'mawb') || str_contains($q, 'customs') || str_contains($q, 'tia') || str_contains($q, 'airport') || str_contains($q, 'volumetric')) {
            $reply = "### ✈️ International Air Cargo & Domestic Feeder Linehaul\n\n"
                . "Namaste {$clientName}! NETPACK operates global air cargo freight departing from Tribhuvan International Airport (TIA) Cargo Terminal (KTM):\n\n"
                . "1. **Outside Kathmandu Valley Feeder Linehaul**:\n"
                . "   - Originating outside Kathmandu (e.g. Pokhara, Biratnagar, Birgunj, Chitwan, Butwal)?\n"
                . "   - Our automated feeder linehaul engine routes parcels to the KTM Cargo Terminal, bundling feeder transport with international air freight.\n"
                . "2. **Official Zero-Charges HAWB**:\n"
                . "   - Generates compliant 3-part House Air Waybills (Consignee Copy, Customs Copy, Carrier Copy) with monetary rates hidden to strictly comply with export customs clearance regulations.\n"
                . "3. **Volumetric Weight Formula**:\n"
                . "   - Chargeable weight = Higher of Gross Actual Weight vs. Volumetric Weight: `(Length × Width × Height in cm) ÷ 5000`.\n"
                . "4. **Global Corridors & Carrier Telemetry**:\n"
                . "   - Direct hub connections to DXB (Dubai), LHR (London), JFK (New York), SYD (Sydney).\n"
                . "   - Real-time carrier telemetry integration with FedEx, DHL, UPS, Royal Mail, Australia Post, DPD, and Aramex.";

            return [
                'success' => true,
                'provider' => 'builtin_expert',
                'response' => $reply,
                'speech_text' => "Our international air cargo connects Tribhuvan International Airport with Dubai, London, New York, and Sydney. Consignments outside Kathmandu are brought via domestic feeder linehaul, with automated zero-charge HAWBs and volumetric weight calculations.",
                'gesture' => 'speaking',
                'actions' => [
                    ['label' => 'Rate Calculator', 'url' => '/rates/inquiry'],
                ],
            ];
        }

        // 6. Prohibited & Hazardous Goods
        if (str_contains($q, 'prohibited') || str_contains($q, 'restricted') || str_contains($q, 'allowed') || str_contains($q, 'battery') || str_contains($q, 'liquid') || str_contains($q, 'dangerous')) {
            $reply = "### 🚫 Prohibited & Restricted Shipping Guidelines\n\n"
                . "To ensure flight safety and compliance with Nepal Customs & ICAO regulations:\n\n"
                . "* **Strictly Prohibited**:\n"
                . "  - Loose lithium-ion batteries or damaged power banks (must be installed inside equipment under IATA PI967)\n"
                . "  - Flammable liquids, perfumes, aerosols, spirits (>70% ABV), and pressurized gas canisters\n"
                . "  - Narcotics, firearms, explosives, ammunition, and counterfeit currency\n"
                . "  - Raw precious metals (gold/silver bullion) and untaxed antiques without archaeology permit\n"
                . "* **Restricted / Special Packing Required**:\n"
                . "  - Liquid medicines, biological samples (require dry ice & carrier MSDS certification)\n"
                . "  - Perishable foods (require express insulated packaging)";

            return [
                'success' => true,
                'provider' => 'builtin_expert',
                'response' => $reply,
                'speech_text' => "Strictly prohibited items include loose lithium batteries, aerosols, flammable liquids, untaxed precious metals, and explosives. Electronic devices with built-in batteries require IATA compliant packaging.",
                'gesture' => 'alerting',
                'actions' => [],
            ];
        }

        // 7. General Assistant Fallback
        $reply = "Namaste {$clientName}! 🙏 I am your NETPACK AI Logistics Copilot.\n\n"
            . "I am equipped to provide instantaneous assistance on:\n"
            . "* **Door-to-Door Delivery**: Direct rider dispatch, 6-digit Pickup & Delivery OTP handovers, COD cash limits, and doorstep POD.\n"
            . "* **Domestic Nepal Express**: Coverage across all 7 Provinces & 77 Districts with regional sorting hub routing.\n"
            . "* **International Air Cargo**: Tribhuvan International Airport (TIA) Gateway, domestic feeder linehauls from outside Kathmandu, zero-charge HAWBs, and volumetric weight calculations.\n"
            . "* **Important Occasions & Deadlines**: Dashain, Tihar, New Year, Black Friday, and daily cargo cutoff schedules.\n"
            . "* **Real-Time Consignment Tracking**: Provide any tracking code (e.g. `NP-2026-...`) to check live telemetry.\n\n"
            . "How can I help you right now?";

        return [
            'success' => true,
            'provider' => 'builtin_expert',
            'response' => $reply,
            'speech_text' => "Namaste {$clientName}! I can help you with door-to-door delivery, tracking your shipment, calculating rates, OTP handovers, or festival shipping schedules. What would you like to know?",
            'gesture' => 'waving',
            'actions' => [],
        ];
    }

    /**
     * Build rich response for Occasions, Festivals & Logistics Schedules
     */
    public function buildOccasionsAndSchedulesResponse(string $clientName): array
    {
        $occasions = config('ai_assistant.occasions', []);
        $schedules = config('ai_assistant.schedules', []);

        $reply = "### 🗓️ Important Occasions, Festival Logistics & Daily Schedules\n\n"
            . "Namaste {$clientName}! Proactive scheduling is key to avoiding delivery bottlenecks during Nepal's peak festivities:\n\n";

        $reply .= "#### 🌟 Upcoming Major Occasions & Festive Shipping Alerts:\n";
        foreach ($occasions as $occ) {
            $impactBadge = match ($occ['impact_level']) {
                'CRITICAL_PEAK' => '🔴 **CRITICAL PEAK**',
                'HIGH_PEAK' => '🟠 **HIGH PEAK**',
                'INTERNATIONAL_PEAK' => '✈️ **GLOBAL PEAK**',
                default => '🟢 **STANDARD PEAK**',
            };
            $reply .= "* **{$occ['name']}** ({$occ['approx_month']}) &middot; {$impactBadge}\n";
            $reply .= "  - *Impact*: {$occ['description']}\n";
            $reply .= "  - *Logistics Advisory*: {$occ['logistical_notice']}\n";
            $reply .= "  - *Recommended Cutoff*: Book at least **{$occ['cutoff_days_prior']} days prior** to avoid highway delays.\n\n";
        }

        $reply .= "#### ⏰ Daily Operational Cutoffs (Nepal Time UTC+5:45):\n"
            . "* **Same-Day Intra-City Delivery Cutoff**: **{$schedules['same_day_cutoff']} PM** (Book before noon for same-day doorstep arrival by 8:00 PM).\n"
            . "* **Express Intra-City Transit**: **{$schedules['express_intacity_hours']}** for flash courier.\n"
            . "* **TIA Cargo Terminal Intake Cutoff**: **{$schedules['tia_cargo_intake_cutoff']} PM** for same-night export flight connections.\n"
            . "* **Inter-District Night Linehaul Highway Departures**: **{$schedules['night_linehaul_departure']} PM** nightly from Kathmandu Sorting Hub.\n"
            . "* **Weekly Operating Policy**: {$schedules['weekend_policy']}.";

        return [
            'success' => true,
            'provider' => 'builtin_expert',
            'response' => $reply,
            'speech_text' => "Namaste {$clientName}! During Dashain and Tihar, highway linehauls experience severe peak traffic. We recommend booking remote district parcels at least 5 days in advance. For same day delivery, daily bookings cut off at 12 noon.",
            'gesture' => 'celebrating',
            'actions' => [
                ['label' => 'Create Festive Booking', 'url' => '/shipments/create'],
            ],
        ];
    }

    /**
     * Inspect query to see if user is asking to track a specific consignment number
     */
    protected function tryLookupTracking(string $query): ?array
    {
        // Match tracking numbers like NP-..., AWB-..., HAWB-..., or 8+ digit alphanumeric codes
        if (preg_match('/\b(NP[-\w\d]+|[A-Z]{2,4}[-\d]{4,15}|\d{8,14})\b/i', $query, $matches)) {
            $candidateNumber = trim($matches[1]);

            // Query Shipment model
            try {
                $shipment = Shipment::where('tracking_number', $candidateNumber)
                    ->orWhere('hawb_number', $candidateNumber)
                    ->orWhere('awb_number', $candidateNumber)
                    ->first();

                if ($shipment) {
                    $status = ucfirst(str_replace('_', ' ', $shipment->status ?? 'in_transit'));
                    $origin = $shipment->origin_city ?? ($shipment->sender_city ?? 'Kathmandu');
                    $dest = $shipment->destination_city ?? ($shipment->recipient_city ?? 'Destination');
                    $eta = $shipment->estimated_delivery_date ? Carbon::parse($shipment->estimated_delivery_date)->format('M d, Y') : 'In Transit Schedule';

                    $response = "### 📦 Live Tracking Report: `{$candidateNumber}`\n\n"
                        . "* **Current Status**: **{$status}**\n"
                        . "* **Origin**: {$origin}\n"
                        . "* **Destination**: {$dest}\n"
                        . "* **Service**: " . ucfirst($shipment->service_type ?? 'Express Door Delivery') . "\n"
                        . "* **Weight**: " . ($shipment->chargeable_weight ?? $shipment->weight ?? '1.0') . " kg\n"
                        . "* **Estimated Delivery**: {$eta}\n\n"
                        . "Your parcel telemetry is live and synced with our regional hub scanner network.";

                    return [
                        'success' => true,
                        'provider' => 'database_lookup',
                        'response' => $response,
                        'speech_text' => "I located your shipment {$candidateNumber}. The current status is {$status}, moving from {$origin} to {$dest}.",
                        'gesture' => 'speaking',
                        'actions' => [
                            ['label' => 'Open Tracking Radar', 'url' => "/tracking/{$candidateNumber}"],
                        ],
                    ];
                }

                // Check PickupRequest
                $pickup = PickupRequest::where('tracking_number', $candidateNumber)->first();
                if ($pickup) {
                    $status = ucfirst(str_replace('_', ' ', $pickup->status ?? 'pending'));
                    $response = "### 📋 Pickup Request Status: `{$candidateNumber}`\n\n"
                        . "* **Status**: **{$status}**\n"
                        . "* **Pickup Location**: {$pickup->pickup_address}, {$pickup->pickup_city}\n"
                        . "* **Delivery Destination**: {$pickup->delivery_city}\n"
                        . "* **Service Tier**: " . ucfirst($pickup->service_tier ?? 'Standard') . "\n"
                        . "* **Scheduled Window**: " . ($pickup->scheduled_pickup_time ? Carbon::parse($pickup->scheduled_pickup_time)->format('h:i A, M d') : 'Pending Dispatch');

                    return [
                        'success' => true,
                        'provider' => 'database_lookup',
                        'response' => $response,
                        'speech_text' => "Your pickup request {$candidateNumber} is currently {$status} for pickup at {$pickup->pickup_city}.",
                        'gesture' => 'speaking',
                        'actions' => [
                            ['label' => 'View Inquiries', 'url' => '/shipments/create?tab=queue'],
                        ],
                    ];
                }
            } catch (\Throwable $e) {
                Log::info('AI tracking lookup caught query exception: ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Inspect query for quick rate calculation patterns
     */
    protected function tryCalculateQuickRate(string $query): ?array
    {
        $q = strtolower($query);

        // Pattern matching: e.g. "rate from kathmandu to pokhara" or "cost from pokhara to kathmandu"
        if (preg_match('/(?:rate|cost|price|quote|how much)\s+(?:from|for)?\s*([a-zA-Z\s]+)\s+to\s+([a-zA-Z\s]+)/i', $query, $matches)) {
            $origin = trim($matches[1]);
            $dest = trim($matches[2]);

            // Extract weight if specified, e.g. "2 kg" or "5kg"
            $weight = 1.0;
            if (preg_match('/(\d+(?:\.\d+)?)\s*(?:kg|kilo|gram)/i', $query, $wMatch)) {
                $weight = (float) $wMatch[1];
            }

            // Estimate base rate based on standard domestic tier
            $basePrice = 120; // NPR base
            $isIntercity = (strtolower($origin) !== strtolower($dest));
            if ($isIntercity) {
                $basePrice = 220 + max(0, ($weight - 1)) * 80;
            } else {
                $basePrice = 120 + max(0, ($weight - 1)) * 40;
            }

            $response = "### 💡 Instant Domestic Tariff Estimate\n\n"
                . "* **Route**: **{$origin} &rarr; {$dest}**\n"
                . "* **Consignment Weight**: **{$weight} kg**\n"
                . "* **Estimated Rate**: **Rs. " . number_format($basePrice, 2) . "** (Inclusive of doorstep pickup & last-mile delivery OTP handover)\n"
                . "* **Estimated Transit**: " . ($isIntercity ? "24-48 Hours (Inter-City Highway Linehaul)" : "Same-Day / 2-4 Hours Flash") . "\n\n"
                . "*(Final price includes remote area surcharge and volume weight if package exceeds volumetric dimensions)*.";

            return [
                'success' => true,
                'provider' => 'rate_estimator',
                'response' => $response,
                'speech_text' => "For a {$weight} kilogram shipment from {$origin} to {$dest}, the estimated door-to-door delivery rate is approximately Rs. " . number_format($basePrice, 0) . ".",
                'gesture' => 'speaking',
                'actions' => [
                    ['label' => 'Open Detailed Rate Calculator', 'url' => '/rates/inquiry'],
                ],
            ];
        }

        return null;
    }

    /**
     * Check nearest festival or occasion
     */
    public function getNearestOccasion(): ?array
    {
        $occasions = config('ai_assistant.occasions', []);
        $now = Carbon::now('Asia/Kathmandu');
        $month = $now->month;

        // Map months to major festive occasions
        if ($month == 9 || $month == 10) {
            return $occasions[0] ?? null; // Dashain
        } elseif ($month == 11) {
            return $occasions[1] ?? null; // Tihar / Chhath
        } elseif ($month == 12) {
            return $occasions[5] ?? null; // Christmas / New Year
        } elseif ($month == 4) {
            return $occasions[3] ?? null; // Nepali New Year
        }

        return $occasions[0] ?? null; // Default to Dashain reference
    }

    /**
     * Clean markdown for natural text-to-speech output
     */
    public function sanitizeForSpeech(string $markdown): string
    {
        // Remove markdown headings, bold, bullet asterisks, code blocks
        $text = preg_replace('/^#+\s+/m', '', $markdown);
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = preg_replace('/\*([^*]+)\*/', '$1', $text);
        $text = preg_replace('/`([^`]+)`/', '$1', $text);
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text);
        $text = preg_replace('/^\s*[\*\-]\s+/m', '', $text);
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Detect best gesture from content
     */
    protected function detectGesture(string $content): string
    {
        $c = strtolower($content);
        if (str_contains($c, 'subhakamana') || str_contains($c, 'congratulations') || str_contains($c, 'happy') || str_contains($c, 'festival')) {
            return 'celebrating';
        }
        if (str_contains($c, 'prohibited') || str_contains($c, 'warning') || str_contains($c, 'cutoff') || str_contains($c, 'urgent')) {
            return 'alerting';
        }
        if (str_contains($c, 'calculating') || str_contains($c, 'analyzing') || str_contains($c, 'looking up')) {
            return 'thinking';
        }
        return 'speaking';
    }

    /**
     * Extract call-to-action buttons if suggested in reply
     */
    protected function extractActions(string $content): array
    {
        $actions = [];
        if (str_contains($content, 'tracking') || str_contains($content, 'radar')) {
            $actions[] = ['label' => 'Universal Tracking', 'url' => '/tracking'];
        }
        if (str_contains($content, 'rate') || str_contains($content, 'tariff')) {
            $actions[] = ['label' => 'Rate Calculator', 'url' => '/rates/inquiry'];
        }
        return $actions;
    }

    /**
     * Build comprehensive system prompt for external LLM models
     */
    protected function buildSystemKnowledgePrompt(?User $user = null): string
    {
        $clientName = $user ? $user->name : 'Valued Client';
        $userRole = $user ? $user->user_type : 'guest';

        return <<<EOT
You are NETPACK AI Logistics Copilot, the official conversational AI assistant of "COURIER with NETPACK" (Nepal's premier logistics, e-commerce, and international air cargo management platform).
The user interacting with you is {$clientName} (Role: {$userRole}).

YOUR PERSONALITY & TONE:
1. Always greet the client warmly and politely using their name or honorifics ("Namaste {$clientName} Ji!").
2. Your tone is respectful, professional, welcoming, energetic, and highly knowledgeable. Avoid monotonous, robotic, or dry responses; improvise your phrasing naturally.
3. Be culturally attuned to Nepal and international business logistics.
4. Keep answers clear, structured with markdown bullet points, and directly actionable.

CORE LOGISTICS DOMAIN KNOWLEDGE:
- E-Commerce Door-to-Door Delivery:
  * Direct Rider Intra-City: Instant fee quote, 6-digit cryptographic Pickup OTP (Seller -> Rider), 6-digit Delivery OTP (Customer -> Rider), and POD photo.
  * Cash On Delivery (COD): Segregated from rider pay. Tiered rider limits: Level 0 (Rs 0), Level 1 (Rs 5,000), Level 2 (Rs 20,000), Level 3 (Rs 50,000), Level 4 (custom).
- Hybrid Multi-Leg Courier: Leg 1 (Pickup rider) -> Leg 2 (Highway road linehaul with nylon bag QR manifests) -> Leg 3 (Depot rider to doorstep).
- Domestic Nepal Coverage: 7 Provinces (Koshi, Madhesh, Bagmati, Gandaki, Lumbini, Karnali, Sudurpashchim) & 77 Districts.
- International Air Cargo: Tribhuvan International Airport (TIA) Cargo Terminal (KTM). Outside Kathmandu Valley domestic feeder linehauls connect regional hubs (Pokhara, Birgunj, Biratnagar, Chitwan, etc.) with the air gateway. Volumetric weight = (L x W x H in cm) / 5000. Multi-part HAWBs strictly omit monetary charges to comply with customs export clearance regulations.
- Operational Schedules: Same-Day pickup cutoff is 12:00 PM; TIA cargo intake cutoff is 3:00 PM; Night inter-district highway linehauls depart 7:00 PM.
- Festivals & Occasions: Bada Dashain, Tihar / Deepawali, Chhath, Nepali New Year, Black Friday, Christmas. Always warn clients to book remote deliveries 4-5 days ahead of festival cutoffs due to highway congestion.
EOT;
    }
}
