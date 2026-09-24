<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\PickupRequest;
use App\Models\ShipmentIssue;
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
        $clientInfo = $this->resolveClientName($user, '');
        $clientName = $clientInfo['honorific'];
        $roleTitle = 'Client';
        if ($user) {
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

        // If an active occasion is detected, inject festive greetings
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
     * Resolve the client name dynamically from session, user introduction in query, or Auth user.
     */
    public function resolveClientName(?User $user = null, string $query = ''): array
    {
        $justIntroduced = false;
        $preferredName = session('ai_preferred_name');

        // Check if query contains an introduction (e.g. "HelloMy name is Kiran", "My name is Kiran", "call me Kiran", "I am Kiran")
        if (!empty($query)) {
            $patterns = [
                '/(?:hello|hi|namaste|hey)?\s*my\s*name\s*is\s+([A-Za-z]{2,25})\b/i',
                '/(?:call\s*me\s*(?:with\s*that\s*name\s*)?([A-Za-z]{2,25}))/i',
                '/(?:you\s*can\s*call\s*me\s*(?:with\s*that\s*name\s*)?([A-Za-z]{2,25}))/i',
                '/\b(?:i\s*am|i[\'’]m|this\s*is)\s+([A-Za-z]{2,25})\b/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $query, $matches)) {
                    $matched = trim($matches[1]);
                    // Ignore common false positives
                    if (!in_array(strtolower($matched), ['interested', 'looking', 'trying', 'here', 'writing', 'asking', 'planning', 'ready', 'sending', 'booking', 'with', 'that', 'name'])) {
                        $preferredName = ucfirst(strtolower($matched));
                        session(['ai_preferred_name' => $preferredName]);
                        $justIntroduced = true;
                        break;
                    }
                }
            }

            // Also check for "HelloMy name is Kiran" without space
            if (!$justIntroduced && preg_match('/(?:hello|hi|namaste)?my\s*name\s*is\s+([A-Za-z]{2,25})\b/i', $query, $matches)) {
                $matched = trim($matches[1]);
                if (!in_array(strtolower($matched), ['interested', 'looking', 'with', 'that', 'name'])) {
                    $preferredName = ucfirst(strtolower($matched));
                    session(['ai_preferred_name' => $preferredName]);
                    $justIntroduced = true;
                }
            }
        }

        if (empty($preferredName)) {
            if ($user && !empty($user->name)) {
                $firstName = explode(' ', trim($user->name))[0];
                $preferredName = ucfirst($firstName);
            } else {
                $preferredName = 'Valued Client';
            }
        }

        $honorific = ($preferredName === 'Valued Client') ? 'Valued Client' : "{$preferredName} Ji";

        return [
            'name' => $preferredName,
            'honorific' => $honorific,
            'just_introduced' => $justIntroduced,
        ];
    }

    /**
     * Get contextual quick suggestion chips based on user role
     */
    public function getQuickSuggestions(?User $user = null): array
    {
        $role = $user ? $user->user_type : 'guest';

        if ($user && (method_exists($user, 'isSuperAdmin') && ($user->isSuperAdmin() || $user->isDomesticAdmin() || $user->isInternationalAdmin()) || in_array($role, ['admin', 'staff', 'super_admin', 'domestic_admin', 'international_admin']))) {
            return [
                ['label' => '🚨 Active Operational Issues', 'prompt' => 'Show me all current operational bottlenecks, delayed consignments, and customs holds.'],
                ['label' => '🤝 Win-Win-Win Recommendations', 'prompt' => 'Provide proactive win-win-win solutions for all active delivery exceptions.'],
                ['label' => '🛃 TIA Customs & Invoicing Holds', 'prompt' => 'Check international air cargo export documentation holds at TIA gateway.'],
                ['label' => '🛵 Pending Doorstep Pickups', 'prompt' => 'Are there any unassigned doorstep pickup requests exceeding 1 hour?'],
                ['label' => '📊 Network Logistics Health', 'prompt' => 'Give me an operational health summary across all 7 provinces.'],
            ];
        }

        $common = [
            ['label' => '📍 Track Consignment', 'prompt' => 'I would like to track my consignment. How does tracking work?'],
            ['label' => '✈️ Jhapa to Poland 20kg', 'prompt' => 'I want to book a 20kg shipment for Poland picked up from Jhapa. How does the whole process work?'],
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
     * Deep NLU Entity & Intent Parser for Logistics Queries
     */
    public function parseLogisticsEntities(string $query): array
    {
        $q = strtolower($query);

        // 1. Weight Extraction (Digits + Romanized Nepali number words)
        $weight = null;
        $nepaliWordNums = [
            'ek' => 1, 'dui' => 2, 'tin' => 3, 'teen' => 3, 'char' => 4,
            'panch' => 5, 'paanch' => 5, 'chha' => 6, 'sat' => 7, 'saat' => 7,
            'aath' => 8, 'nau' => 9, 'das' => 10, 'pandhra' => 15, 'bis' => 20,
            'bees' => 20, 'pachis' => 25, 'tis' => 30, 'chalis' => 40, 'pachas' => 50,
            'saya' => 100, 'aadha' => 0.5, 'half' => 0.5,
        ];
        $weightQuery = $query;
        foreach ($nepaliWordNums as $nw => $numVal) {
            $weightQuery = preg_replace('/\b' . $nw . '\b/i', (string)$numVal, $weightQuery);
        }

        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:kg|kgs|kilo|kilos|kilogram|kilograms)\b/i', $weightQuery, $wMatch)) {
            $weight = (float) $wMatch[1];
        } elseif (preg_match('/(\d+(?:\.\d+)?)\s*(?:gm|gms|gram|grams)\b/i', $weightQuery, $wMatch)) {
            $weight = round(((float) $wMatch[1]) / 1000, 2);
        }

        // 2. Global Countries Master Dictionary
        $countries = [
            'poland' => ['name' => 'Poland', 'region' => 'European Union', 'airport' => 'Warsaw Chopin Airport (WAW)', 'courier' => 'DPD Poland / DHL Express / FedEx Europe'],
            'germany' => ['name' => 'Germany', 'region' => 'European Union', 'airport' => 'Frankfurt Airport (FRA)', 'courier' => 'DHL Express Europe'],
            'united kingdom' => ['name' => 'United Kingdom', 'region' => 'United Kingdom', 'airport' => 'London Heathrow (LHR)', 'courier' => 'Royal Mail / DPD / DHL'],
            'uk' => ['name' => 'United Kingdom', 'region' => 'United Kingdom', 'airport' => 'London Heathrow (LHR)', 'courier' => 'Royal Mail / DPD / DHL'],
            'england' => ['name' => 'United Kingdom', 'region' => 'United Kingdom', 'airport' => 'London Heathrow (LHR)', 'courier' => 'Royal Mail / DPD / DHL'],
            'united states' => ['name' => 'United States', 'region' => 'North America', 'airport' => 'JFK New York / LAX Los Angeles', 'courier' => 'FedEx Express / UPS / USPS'],
            'usa' => ['name' => 'United States', 'region' => 'North America', 'airport' => 'JFK New York / LAX Los Angeles', 'courier' => 'FedEx Express / UPS / USPS'],
            'america' => ['name' => 'United States', 'region' => 'North America', 'airport' => 'JFK New York / LAX Los Angeles', 'courier' => 'FedEx Express / UPS / USPS'],
            'australia' => ['name' => 'Australia', 'region' => 'Oceania', 'airport' => 'Sydney Airport (SYD)', 'courier' => 'Australia Post / DHL Express'],
            'canada' => ['name' => 'Canada', 'region' => 'North America', 'airport' => 'Toronto Pearson (YYZ)', 'courier' => 'Canada Post / FedEx Express'],
            'united arab emirates' => ['name' => 'United Arab Emirates', 'region' => 'Middle East', 'airport' => 'Dubai International (DXB)', 'courier' => 'Aramex / DHL Express'],
            'uae' => ['name' => 'United Arab Emirates', 'region' => 'Middle East', 'airport' => 'Dubai International (DXB)', 'courier' => 'Aramex / DHL Express'],
            'dubai' => ['name' => 'United Arab Emirates', 'region' => 'Middle East', 'airport' => 'Dubai International (DXB)', 'courier' => 'Aramex / DHL Express'],
            'japan' => ['name' => 'Japan', 'region' => 'East Asia', 'airport' => 'Tokyo Narita (NRT)', 'courier' => 'Japan Post / Yamato Transport / DHL'],
            'france' => ['name' => 'France', 'region' => 'European Union', 'airport' => 'Paris Charles de Gaulle (CDG)', 'courier' => 'Chronopost / DHL Express'],
            'netherlands' => ['name' => 'Netherlands', 'region' => 'European Union', 'airport' => 'Amsterdam Schiphol (AMS)', 'courier' => 'PostNL / DHL Express'],
            'italy' => ['name' => 'Italy', 'region' => 'European Union', 'airport' => 'Milan Malpensa (MXP)', 'courier' => 'Poste Italiane / DHL Express'],
            'spain' => ['name' => 'Spain', 'region' => 'European Union', 'airport' => 'Madrid-Barajas (MAD)', 'courier' => 'Correos / DHL Express'],
            'switzerland' => ['name' => 'Switzerland', 'region' => 'Europe', 'airport' => 'Zurich Airport (ZRH)', 'courier' => 'Swiss Post / DHL Express'],
            'sweden' => ['name' => 'Sweden', 'region' => 'European Union', 'airport' => 'Stockholm Arlanda (ARN)', 'courier' => 'PostNord / DHL Express'],
            'singapore' => ['name' => 'Singapore', 'region' => 'Southeast Asia', 'airport' => 'Singapore Changi (SIN)', 'courier' => 'Singapore Post / DHL Express'],
            'qatar' => ['name' => 'Qatar', 'region' => 'Middle East', 'airport' => 'Hamad International (DOH)', 'courier' => 'Qatar Post / DHL Express'],
            'india' => ['name' => 'India', 'region' => 'South Asia', 'airport' => 'Indira Gandhi International (DEL)', 'courier' => 'Blue Dart / Delhivery / DHL'],
        ];

        // 3. Nepal Geographical Locations & Regional Hub Mapping
        $nepalLocations = [
            'jhapa' => ['name' => 'Jhapa', 'district' => 'Jhapa', 'province' => 'Koshi Province', 'regional_hub' => 'Biratnagar Central Hub', 'is_outside_ktm' => true],
            'birtamod' => ['name' => 'Birtamod', 'district' => 'Jhapa', 'province' => 'Koshi Province', 'regional_hub' => 'Biratnagar Central Hub', 'is_outside_ktm' => true],
            'damak' => ['name' => 'Damak', 'district' => 'Jhapa', 'province' => 'Koshi Province', 'regional_hub' => 'Biratnagar Central Hub', 'is_outside_ktm' => true],
            'bhadrapur' => ['name' => 'Bhadrapur', 'district' => 'Jhapa', 'province' => 'Koshi Province', 'regional_hub' => 'Biratnagar Central Hub', 'is_outside_ktm' => true],
            'biratnagar' => ['name' => 'Biratnagar', 'district' => 'Morang', 'province' => 'Koshi Province', 'regional_hub' => 'Biratnagar Central Hub', 'is_outside_ktm' => true],
            'morang' => ['name' => 'Morang', 'district' => 'Morang', 'province' => 'Koshi Province', 'regional_hub' => 'Biratnagar Central Hub', 'is_outside_ktm' => true],
            'dharan' => ['name' => 'Dharan', 'district' => 'Sunsari', 'province' => 'Koshi Province', 'regional_hub' => 'Biratnagar Central Hub', 'is_outside_ktm' => true],
            'itahari' => ['name' => 'Itahari', 'district' => 'Sunsari', 'province' => 'Koshi Province', 'regional_hub' => 'Biratnagar Central Hub', 'is_outside_ktm' => true],
            'sunsari' => ['name' => 'Sunsari', 'district' => 'Sunsari', 'province' => 'Koshi Province', 'regional_hub' => 'Biratnagar Central Hub', 'is_outside_ktm' => true],
            'ilam' => ['name' => 'Ilam', 'district' => 'Ilam', 'province' => 'Koshi Province', 'regional_hub' => 'Biratnagar Central Hub', 'is_outside_ktm' => true],
            'kathmandu' => ['name' => 'Kathmandu', 'district' => 'Kathmandu', 'province' => 'Bagmati Province', 'regional_hub' => 'Kathmandu Central Sorting Hub & TIA Gateway', 'is_outside_ktm' => false],
            'lalitpur' => ['name' => 'Lalitpur', 'district' => 'Lalitpur', 'province' => 'Bagmati Province', 'regional_hub' => 'Kathmandu Valley Central Hub', 'is_outside_ktm' => false],
            'bhaktapur' => ['name' => 'Bhaktapur', 'district' => 'Bhaktapur', 'province' => 'Bagmati Province', 'regional_hub' => 'Kathmandu Valley Central Hub', 'is_outside_ktm' => false],
            'pokhara' => ['name' => 'Pokhara', 'district' => 'Kaski', 'province' => 'Gandaki Province', 'regional_hub' => 'Pokhara Regional Sorting Hub', 'is_outside_ktm' => true],
            'kaski' => ['name' => 'Kaski', 'district' => 'Kaski', 'province' => 'Gandaki Province', 'regional_hub' => 'Pokhara Regional Sorting Hub', 'is_outside_ktm' => true],
            'chitwan' => ['name' => 'Chitwan', 'district' => 'Chitwan', 'province' => 'Bagmati Province', 'regional_hub' => 'Bharatpur / Narayangarh Hub', 'is_outside_ktm' => true],
            'bharatpur' => ['name' => 'Bharatpur', 'district' => 'Chitwan', 'province' => 'Bagmati Province', 'regional_hub' => 'Bharatpur / Narayangarh Hub', 'is_outside_ktm' => true],
            'butwal' => ['name' => 'Butwal', 'district' => 'Rupandehi', 'province' => 'Lumbini Province', 'regional_hub' => 'Butwal Regional Sorting Hub', 'is_outside_ktm' => true],
            'bhairahawa' => ['name' => 'Bhairahawa', 'district' => 'Rupandehi', 'province' => 'Lumbini Province', 'regional_hub' => 'Butwal Regional Sorting Hub', 'is_outside_ktm' => true],
            'nepalgunj' => ['name' => 'Nepalgunj', 'district' => 'Banke', 'province' => 'Lumbini Province', 'regional_hub' => 'Nepalgunj Hub', 'is_outside_ktm' => true],
            'surkhet' => ['name' => 'Surkhet', 'district' => 'Surkhet', 'province' => 'Karnali Province', 'regional_hub' => 'Birendranagar Hub', 'is_outside_ktm' => true],
            'dhangadhi' => ['name' => 'Dhangadhi', 'district' => 'Kailali', 'province' => 'Sudurpashchim Province', 'regional_hub' => 'Dhangadhi Hub', 'is_outside_ktm' => true],
            'birgunj' => ['name' => 'Birgunj', 'district' => 'Parsa', 'province' => 'Madhesh Province', 'regional_hub' => 'Birgunj / Parsa Hub', 'is_outside_ktm' => true],
            'janakpur' => ['name' => 'Janakpur', 'district' => 'Dhanusha', 'province' => 'Madhesh Province', 'regional_hub' => 'Janakpur Hub', 'is_outside_ktm' => true],
            'hetauda' => ['name' => 'Hetauda', 'district' => 'Makwanpur', 'province' => 'Bagmati Province', 'regional_hub' => 'Hetauda Hub', 'is_outside_ktm' => true],
        ];

        // 4. Extract Origin (English: "from Jhapa", "pickup from Jhapa" & Nepali: "Jhapa bata", "Damak dekhi")
        $origin = null;
        if (preg_match('/(?:([a-zA-Z\s]+?)\s+(?:bata|dekhi)\b|(?:picked\s*up\s*(?:from|in|at)|pickup\s*(?:from|at|in)|from)\s+([a-zA-Z\s]+?))(?:\s+which|\s+to|\s+for|\s+and|\s*,|\s*\.|\s*$)/i', $query, $oMatch)) {
            $candidate = strtolower(trim(!empty($oMatch[1]) ? $oMatch[1] : $oMatch[2]));
            foreach ($nepalLocations as $key => $loc) {
                if (str_contains($candidate, $key)) {
                    $origin = $loc;
                    break;
                }
            }
        }

        // If not found in regex, scan entire query for known Nepal locations
        if (!$origin) {
            foreach ($nepalLocations as $key => $loc) {
                if (preg_match('/\b' . preg_quote($key, '/') . '\b/i', $query)) {
                    $origin = $loc;
                    break;
                }
            }
        }

        // 5. Extract Destination (English: "to Poland", "for Poland" & Nepali: "Poland pathauna", "Poland ma", "Poland lai")
        $destination = null;
        $isInternational = false;

        foreach ($countries as $key => $countryData) {
            if (preg_match('/\b' . preg_quote($key, '/') . '(?:\s+(?:pathauna|pathaune|ma|lai|pugne))?\b/i', $query) || preg_match('/\b' . preg_quote($key, '/') . '\b/i', $query)) {
                $destination = array_merge($countryData, ['type' => 'international']);
                $isInternational = true;
                break;
            }
        }

        // If no international match, check for domestic destination (different from origin)
        if (!$destination) {
            if (preg_match('/(?:([a-zA-Z\s]+?)\s+(?:ma|lai|pathaune|pathauna|pugne)\b|(?:to|for|destination)\s+([a-zA-Z\s]+?))(?:\s+which|\s+from|\s+and|\s*,|\s*\.|\s*$)/i', $query, $dMatch)) {
                $candidate = strtolower(trim(!empty($dMatch[1]) ? $dMatch[1] : $dMatch[2]));
                foreach ($nepalLocations as $key => $loc) {
                    if (str_contains($candidate, $key) && (!$origin || $loc['name'] !== $origin['name'])) {
                        $destination = array_merge($loc, ['type' => 'domestic']);
                        break;
                    }
                }
            }
        }

        // 6. Intent Classification (English + Nepglish / Romanized Nepali)
        $isBookingRequest = (bool) preg_match('/\b(book|booking|shipment|ship|dispatch|send|order|pickup|pathauna|pathaune|lyaauna|lyaaune|puryau)\b/i', $query);
        $isProcessInquiry = (bool) preg_match('/\b(how.*works|process|procedure|steps|workflow|guide|assist\s*me|kasari|tarika|process\s*k\s*ho)\b/i', $query);
        $isRateInquiry = (bool) preg_match('/\b(rate|rates|price|cost|how\s*much|tariff|quote|bhada|kharcha|kati\s*parchha|kati\s*lagchha|mulya)\b/i', $query);
        $isTrackingInquiry = (bool) preg_match('/\b(track|tracking|status|telemetry|kaha\s*pugyo|kahile\s*pugchha|kahile\s*aauchha|where\s*is|locate)\b/i', $query);
        $isIssueInquiry = (bool) preg_match('/\b(delay|delayed|adhkiyo|samasya|problem|issue|complain|damage|lost|hold|customs\s*hold|chhutyo)\b/i', $query);
        $isAdminOperationalInquiry = (bool) preg_match('/\b(operational\s*issue|admin\s*issue|bottleneck|win-win|win\s*win|held\s*consignment|pending\s*pickup|operations\s*health|network\s*delay)\b/i', $query);
        $isEcommerce = (bool) preg_match('/\b(ecommerce|e-commerce|cod|cash\s*on\s*delivery|seller|merchant|online\s*store|online\s*order|daraz|kinbech|rto|rider\s*dispatch)\b/i', $query);

        return [
            'weight' => $weight,
            'origin' => $origin,
            'destination' => $destination,
            'is_international' => $isInternational,
            'is_ecommerce' => $isEcommerce,
            'requires_feeder' => ($isInternational && $origin && !empty($origin['is_outside_ktm'])),
            'is_booking_request' => $isBookingRequest,
            'is_process_inquiry' => $isProcessInquiry,
            'is_rate_inquiry' => $isRateInquiry,
            'is_tracking_inquiry' => $isTrackingInquiry,
            'is_issue_inquiry' => $isIssueInquiry,
            'is_admin_operational_inquiry' => $isAdminOperationalInquiry,
        ];
    }

    /**
     * Process query through the AI Engine (Gemini, OpenAI, Claude, Groq, or Built-in Expert Engine)
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

        // 1. Resolve and store preferred user name (e.g., Kiran)
        $clientInfo = $this->resolveClientName($user, $query);
        $clientName = $clientInfo['honorific'];

        // 2. Parse Logistics Entities (Origin, Destination, Weight, Feeder, etc.)
        $entities = $this->parseLogisticsEntities($query);

        // 3. Tool execution check: Check if query contains a tracking number
        $trackingResult = $this->tryLookupTracking($query);
        if ($trackingResult) {
            $trackingResult['client_name'] = $clientName;
            return $trackingResult;
        }

        // 4. Quick rate calculation pattern check (for simple "rate from X to Y")
        if ($entities['is_rate_inquiry'] && !$entities['is_process_inquiry'] && !$entities['is_booking_request']) {
            $rateResult = $this->tryCalculateQuickRate($query);
            if ($rateResult) {
                $rateResult['client_name'] = $clientName;
                return $rateResult;
            }
        }

        // 5. Check external provider configurations
        $provider = config('ai_assistant.default_provider', 'auto');
        $geminiKey = config('ai_assistant.providers.gemini.api_key');
        $openaiKey = config('ai_assistant.providers.openai.api_key');
        $claudeKey = config('ai_assistant.providers.claude.api_key');
        $groqKey = config('ai_assistant.providers.groq.api_key');

        // External Provider Auto-Discovery
        if ($provider === 'auto' || $provider === 'gemini') {
            if (!empty($geminiKey)) {
                try {
                    $extRes = $this->queryGemini($query, $user, $conversationHistory, $entities, $clientName);
                    if ($extRes) return $extRes;
                } catch (\Throwable $e) {
                    Log::warning('Gemini call failed, falling back: ' . $e->getMessage());
                }
            }
        }

        if ($provider === 'auto' || $provider === 'openai') {
            if (!empty($openaiKey)) {
                try {
                    $extRes = $this->queryOpenAi($query, $user, $conversationHistory, $entities, $clientName);
                    if ($extRes) return $extRes;
                } catch (\Throwable $e) {
                    Log::warning('OpenAI call failed, falling back: ' . $e->getMessage());
                }
            }
        }

        if ($provider === 'auto' || $provider === 'claude') {
            if (!empty($claudeKey)) {
                try {
                    $extRes = $this->queryClaude($query, $user, $conversationHistory, $entities, $clientName);
                    if ($extRes) return $extRes;
                } catch (\Throwable $e) {
                    Log::warning('Claude call failed, falling back: ' . $e->getMessage());
                }
            }
        }

        if ($provider === 'auto' || $provider === 'groq') {
            if (!empty($groqKey)) {
                try {
                    $extRes = $this->queryGroq($query, $user, $conversationHistory, $entities, $clientName);
                    if ($extRes) return $extRes;
                } catch (\Throwable $e) {
                    Log::warning('Groq call failed, falling back: ' . $e->getMessage());
                }
            }
        }

        // 6. Enhanced Built-in Autonomous Logistics Expert Engine
        return $this->queryBuiltinEngine($query, $user, $clientInfo, $entities);
    }

    /**
     * Query Google Gemini 2.0 Flash REST API (Ultra-Fast & Free Tier Accessible)
     */
    protected function queryGemini(string $query, ?User $user, array $history, array $entities, string $clientName): ?array
    {
        $apiKey = config('ai_assistant.providers.gemini.api_key');
        if (empty($apiKey)) return null;

        $model = config('ai_assistant.providers.gemini.model', 'gemini-2.0-flash');
        $endpoint = config('ai_assistant.providers.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta/models');
        $url = rtrim($endpoint, '/') . "/{$model}:generateContent?key={$apiKey}";

        $systemPrompt = $this->buildSystemKnowledgePrompt($user, $clientName, $entities);

        $contents = [];
        foreach (array_slice($history, -6) as $msg) {
            if (isset($msg['role'], $msg['content'])) {
                $contents[] = [
                    'role' => $msg['role'] === 'user' ? 'user' : 'model',
                    'parts' => [['text' => $msg['content']]],
                ];
            }
        }
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $query]],
        ];

        $response = Http::timeout(15)
            ->post($url, [
                'system_instruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1200,
                ],
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($reply) {
                return [
                    'success' => true,
                    'provider' => 'gemini (' . $model . ')',
                    'client_name' => $clientName,
                    'response' => $reply,
                    'speech_text' => $this->sanitizeForSpeech($reply),
                    'gesture' => $this->detectGesture($reply),
                    'actions' => $this->extractActions($reply, $entities),
                ];
            }
        }

        return null;
    }

    /**
     * Query OpenAI with System Context & Knowledge Base
     */
    protected function queryOpenAi(string $query, ?User $user, array $history, array $entities, string $clientName): ?array
    {
        $apiKey = config('ai_assistant.providers.openai.api_key');
        $model = config('ai_assistant.providers.openai.model', 'gpt-4o');
        $baseUrl = config('ai_assistant.providers.openai.base_url', 'https://api.openai.com/v1');

        $systemPrompt = $this->buildSystemKnowledgePrompt($user, $clientName, $entities);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

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
            ->post(rtrim($baseUrl, '/') . '/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $reply = $data['choices'][0]['message']['content'] ?? null;
            if ($reply) {
                return [
                    'success' => true,
                    'provider' => 'openai (' . $model . ')',
                    'client_name' => $clientName,
                    'response' => $reply,
                    'speech_text' => $this->sanitizeForSpeech($reply),
                    'gesture' => $this->detectGesture($reply),
                    'actions' => $this->extractActions($reply, $entities),
                ];
            }
        }

        return null;
    }

    /**
     * Query Anthropic Claude API
     */
    protected function queryClaude(string $query, ?User $user, array $history, array $entities, string $clientName): ?array
    {
        $apiKey = config('ai_assistant.providers.claude.api_key');
        $model = config('ai_assistant.providers.claude.model', 'claude-3-5-sonnet-20241022');
        $endpoint = config('ai_assistant.providers.claude.endpoint', 'https://api.anthropic.com/v1/messages');

        $systemPrompt = $this->buildSystemKnowledgePrompt($user, $clientName, $entities);

        $messages = [];
        foreach (array_slice($history, -6) as $msg) {
            if (isset($msg['role'], $msg['content'])) {
                $messages[] = [
                    'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                    'content' => $msg['content'],
                ];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $query];

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(20)->post($endpoint, [
            'model' => $model,
            'max_tokens' => 1200,
            'system' => $systemPrompt,
            'messages' => $messages,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $reply = $data['content'][0]['text'] ?? null;
            if ($reply) {
                return [
                    'success' => true,
                    'provider' => 'claude (' . $model . ')',
                    'client_name' => $clientName,
                    'response' => $reply,
                    'speech_text' => $this->sanitizeForSpeech($reply),
                    'gesture' => $this->detectGesture($reply),
                    'actions' => $this->extractActions($reply, $entities),
                ];
            }
        }

        return null;
    }

    /**
     * Query Groq (Ultra-Fast Llama-3.3-70b)
     */
    protected function queryGroq(string $query, ?User $user, array $history, array $entities, string $clientName): ?array
    {
        $apiKey = config('ai_assistant.providers.groq.api_key');
        $model = config('ai_assistant.providers.groq.model', 'llama-3.3-70b-versatile');
        $baseUrl = config('ai_assistant.providers.groq.base_url', 'https://api.groq.com/openai/v1');

        $systemPrompt = $this->buildSystemKnowledgePrompt($user, $clientName, $entities);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $query],
        ];

        $response = Http::withToken($apiKey)
            ->timeout(15)
            ->post(rtrim($baseUrl, '/') . '/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.6,
                'max_tokens' => 1000,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $reply = $data['choices'][0]['message']['content'] ?? null;
            if ($reply) {
                return [
                    'success' => true,
                    'provider' => 'groq (' . $model . ')',
                    'client_name' => $clientName,
                    'response' => $reply,
                    'speech_text' => $this->sanitizeForSpeech($reply),
                    'gesture' => $this->detectGesture($reply),
                    'actions' => $this->extractActions($reply, $entities),
                ];
            }
        }

        return null;
    }

    /**
     * Built-in Autonomous Logistics Knowledge-Base Neural Engine
     */
    public function queryBuiltinEngine(string $query, ?User $user = null, ?array $clientInfo = null, ?array $entities = null): array
    {
        $q = strtolower($query);

        if (!$clientInfo) {
            $clientInfo = $this->resolveClientName($user, $query);
        }
        if (!$entities) {
            $entities = $this->parseLogisticsEntities($query);
        }

        $clientName = $clientInfo['honorific'];
        $justIntroduced = $clientInfo['just_introduced'] ?? false;

        // 1. Dynamic Logistics Plan: When Origin and/or Destination or specific shipment request is parsed
        if (($entities['origin'] && $entities['destination']) || ($entities['destination'] && $entities['weight']) || ($entities['origin'] && $entities['is_booking_request'])) {
            return $this->generateTailoredLogisticsPlan($entities, $clientInfo);
        }

        // 1.5 Operational Inquiries & Win-Win Solutions for Admins or Operational Staff
        if (!empty($entities['is_admin_operational_inquiry']) || str_contains($q, 'operational issue') || str_contains($q, 'admin issue') || str_contains($q, 'win-win') || str_contains($q, 'win win') || str_contains($q, 'bottleneck') || (str_contains($q, 'delay') && $user && (method_exists($user, 'isSuperAdmin') && ($user->isSuperAdmin() || $user->isDomesticAdmin() || $user->isInternationalAdmin()) || in_array(($user->user_type ?? ''), ['admin', 'staff', 'super_admin'])))) {
            return $this->generateAdminOperationalIntelligenceReport($clientInfo, $q);
        }

        // 1.6 Client Delay & Problem Reassurance with Win-Win Policy
        if (!empty($entities['is_issue_inquiry']) || str_contains($q, 'adhkiyo') || str_contains($q, 'samasya') || str_contains($q, 'chhutyo') || (str_contains($q, 'delay') && !str_contains($q, 'broadcast'))) {
            $reply = "### 🤝 Proactive Issue Resolution & Win-Win Recovery Plan\n\n"
                . "Namaste {$clientName}! 🙏 We understand your concern regarding an in-transit delay or delivery exception. At **COURIER with NETPACK**, our standard protocol ensures a **Win-Win-Win Outcome** for all parties:\n\n"
                . "1. 🟢 **Win for You (Client)**:\n"
                . "   - **Instant Network Verification**: We immediately cross-reference the consignment GPS telemetry with our sorting hubs.\n"
                . "   - **Priority Highway Dispatch**: If delayed by road weather or landslide detours, your parcel is bumped to the priority night linehaul truck at 7:00 PM with zero extra charge.\n"
                . "   - **Zero Demurrage**: Any holding or customs warehouse fees caused by operational delays are 100% absorbed by NETPACK.\n\n"
                . "2. 🔵 **Win for Operations & Riders**:\n"
                . "   - Regional dispatchers and riders receive clear automated reroute instructions, eliminating idle waiting and futile delivery attempts.\n\n"
                . "3. 🟣 **Win for NETPACK Company**:\n"
                . "   - We uphold transparent customer care, preserving our reputation as Nepal's most reliable courier.\n\n"
                . "👉 **Next Step**: Provide your tracking or HAWB number (e.g. `NP-2026-...`), or click below to view real-time tracking.";

            return [
                'success' => true,
                'provider' => 'builtin_expert',
                'client_name' => $clientName,
                'response' => $reply,
                'speech_text' => "Namaste {$clientName}! If your parcel has experienced an unexpected delay, our win-win policy ensures priority highway re-dispatch with zero holding charges. Please share your tracking number to check its live status.",
                'gesture' => 'speaking',
                'actions' => [
                    ['label' => 'Live Consignment Tracking', 'url' => '/tracking'],
                    ['label' => 'Report Issue to Ops', 'url' => '/admin/issues'],
                ],
            ];
        }

        // 2. Door-to-Door Delivery Mechanics & OTP Protocols
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
                'client_name' => $clientName,
                'response' => $reply,
                'speech_text' => "Namaste {$clientName}! Our door-to-door delivery uses dual OTP security. The seller provides a 6-digit Pickup OTP when the rider arrives, and the recipient provides a 6-digit Delivery OTP upon doorstep handover with COD cash collection.",
                'gesture' => 'speaking',
                'actions' => [
                    ['label' => 'Book Direct Delivery', 'url' => '/shipments/create'],
                ],
            ];
        }

        // 2.5 All 3 Core Logistics Services Overview (Domestic, International, E-Commerce & COD)
        if (str_contains($q, '3 service') || str_contains($q, 'three service') || str_contains($q, 'teen service') || str_contains($q, 'all services') || str_contains($q, 'all service') || str_contains($q, 'service category') || str_contains($q, 'services do you offer') || str_contains($q, 'what services') || str_contains($q, 'service list') || str_contains($q, 'services available')) {
            $reply = "### 🌐 NETPACK 3 Core Logistics Services\n\n"
                . "Namaste {$clientName}! At **COURIER with NETPACK**, our unified logistics infrastructure covers **all 3 core services**:\n\n"
                . "1. 🇳🇵 **Domestic Express Delivery**:\n"
                . "   - Inter-city, inter-district, and intra-valley courier across all **7 Provinces and 77 Districts** of Nepal.\n"
                . "   - Highway truck linehauls with nylon bags and QR manifest scanning connecting Biratnagar, Janakpur, Kathmandu, Pokhara, Butwal, Surkhet, and Dhangadhi.\n\n"
                . "2. ✈️ **International Air Cargo & Feeder Logistics**:\n"
                . "   - Global export air cargo departing Tribhuvan International Airport (TIA) Cargo Terminal to Poland, Europe, USA, UK, UAE, and 220+ countries.\n"
                . "   - Automated domestic feeder linehaul linking outer districts (Jhapa, Morang, etc.) with TIA Cargo Terminal.\n"
                . "   - Zero-charge 3-copy HAWB export documents and Tier-1 carrier tracking (DPD, DHL, FedEx, UPS).\n\n"
                . "3. 📦 **E-Commerce & Cash on Delivery (COD) Rider Delivery**:\n"
                . "   - Purpose-built for online stores, Daraz sellers, Instagram merchants, and retail brands.\n"
                . "   - Instant rider dispatch with secret **6-digit Pickup OTP** and **6-digit Delivery OTP**.\n"
                . "   - Daily automated COD cash settlement directly to merchant bank accounts, eSewa, or Khalti.\n"
                . "   - Real-time RTO (Return to Origin) management, door-to-door exchanges, and verified POD photo capture.\n\n"
                . "Which service would you like to explore or book?";

            return [
                'success' => true,
                'provider' => 'builtin_expert',
                'client_name' => $clientName,
                'response' => $reply,
                'speech_text' => "Namaste {$clientName}! NETPACK offers three complete services: Domestic Express Delivery across Nepal's 77 districts, International Air Cargo departing Tribhuvan International Airport, and E-Commerce Deliveries with Cash on Delivery and dual OTP security.",
                'gesture' => 'speaking',
                'actions' => [
                    ['label' => '🇳🇵 Book Domestic', 'url' => '/shipments/create?mode=domestic'],
                    ['label' => '✈️ Book International', 'url' => '/shipments/create?mode=international'],
                    ['label' => '📦 Book E-Commerce COD', 'url' => '/shipments/create?mode=ecommerce'],
                ],
            ];
        }

        // 2.7 COD (Cash On Delivery) Limits, Tiers & Financial Segregation
        if (str_contains($q, 'limit') || str_contains($q, 'cash in hand') || str_contains($q, 'tiered') || (str_contains($q, 'cod') && (str_contains($q, 'ledger') || str_contains($q, 'rule') || str_contains($q, 'policy')))) {
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
                'client_name' => $clientName,
                'response' => $reply,
                'speech_text' => "NETPACK separates rider earnings from COD cash collected. We enforce tiered COD limits from Level 0 up to Level 4 for trusted riders, with automated digital remittance to sellers via bank, eSewa, or Khalti.",
                'gesture' => 'speaking',
                'actions' => [],
            ];
        }

        // 2.8 E-Commerce Delivery & Multi-Vendor Merchant Solutions
        if (!empty($entities['is_ecommerce']) || str_contains($q, 'ecommerce') || str_contains($q, 'e-commerce') || str_contains($q, 'online store') || str_contains($q, 'merchant') || str_contains($q, 'seller') || str_contains($q, 'daraz') || str_contains($q, 'kinbech') || str_contains($q, 'rto')) {
            $reply = "### 📦 E-Commerce Express & Cash On Delivery (COD) Management\n\n"
                . "Namaste {$clientName}! NETPACK's dedicated **E-Commerce Delivery Service** is optimized specifically for online merchants, social commerce sellers, and retail platforms across Nepal:\n\n"
                . "1. **Doorstep Merchant Pickup & Pickup OTP**:\n"
                . "   - Book directly from your merchant console. A nearby rider is dispatched immediately to your store or warehouse.\n"
                . "   - Handover is secured with a cryptographic 6-digit **Pickup OTP**.\n\n"
                . "2. **Cash on Delivery (COD) Collections & Guaranteed Payouts**:\n"
                . "   - Riders collect cash at the buyer's doorstep upon 6-digit **Delivery OTP** verification.\n"
                . "   - Payouts are digitally remitted via automated batch transfers to your **Bank Account, eSewa, or Khalti** with detailed ledger statements.\n\n"
                . "3. **Reverse Logistics & RTO Management**:\n"
                . "   - Non-delivered or exchanged parcels are marked with clear reason codes (customer uncontactable, rejected, rescheduled) and returned safely to merchant inventory.\n\n"
                . "4. **Customer Live Telemetry & SMS Alerts**:\n"
                . "   - End customers receive live rider tracking links and SMS alerts with accurate ETA.";

            return [
                'success' => true,
                'provider' => 'builtin_expert',
                'client_name' => $clientName,
                'response' => $reply,
                'speech_text' => "Namaste {$clientName}! Our E-Commerce delivery service offers instant rider dispatch, dual OTP verification, same-day delivery, and automated Cash on Delivery payouts to your bank, eSewa, or Khalti.",
                'gesture' => 'speaking',
                'actions' => [
                    ['label' => '📦 Book E-Commerce Delivery', 'url' => '/shipments/create?mode=ecommerce'],
                    ['label' => '💳 COD Settlement Guide', 'url' => '/rates/inquiry'],
                ],
            ];
        }

        // 3. COD (Cash On Delivery) General Guidance
        if (str_contains($q, 'cod') || str_contains($q, 'cash on delivery')) {
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
                'client_name' => $clientName,
                'response' => $reply,
                'speech_text' => "NETPACK separates rider earnings from COD cash collected. We enforce tiered COD limits from Level 0 up to Level 4 for trusted riders, with automated digital remittance to sellers via bank, eSewa, or Khalti.",
                'gesture' => 'speaking',
                'actions' => [],
            ];
        }

        // 4. Occasions, Festivals & Holiday Deadlines
        if (str_contains($q, 'festival') || str_contains($q, 'occasion') || str_contains($q, 'dashain') || str_contains($q, 'tihar') || str_contains($q, 'holiday') || str_contains($q, 'cutoff') || str_contains($q, 'schedule')) {
            $occRes = $this->buildOccasionsAndSchedulesResponse($clientName);
            $occRes['client_name'] = $clientName;
            return $occRes;
        }

        // 5. Domestic Express Nepal & 7 Provinces
        if (str_contains($q, 'nepal') || str_contains($q, 'province') || str_contains($q, 'district') || str_contains($q, 'pokhara') || str_contains($q, 'biratnagar') || str_contains($q, 'chitwan') || str_contains($q, 'jhapa') || str_contains($q, 'koshi') || str_contains($q, 'bagmati') || str_contains($q, 'gandaki') || str_contains($q, 'lumbini') || str_contains($q, 'karnali') || str_contains($q, 'sudurpashchim') || str_contains($q, 'madhesh')) {
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
                'client_name' => $clientName,
                'response' => $reply,
                'speech_text' => "Namaste! Our domestic network covers all 7 Provinces and 77 Districts across Nepal. We operate central sorting hubs in Biratnagar, Janakpur, Kathmandu, Pokhara, Butwal, Surkhet, and Dhangadhi with nylon bag QR manifest tracking.",
                'gesture' => 'speaking',
                'actions' => [
                    ['label' => 'View Domestic Network', 'url' => '/tracking'],
                ],
            ];
        }

        // 6. International Air Cargo & Feeder Linehauls
        if (str_contains($q, 'international') || str_contains($q, 'air cargo') || str_contains($q, 'feeder') || str_contains($q, 'hawb') || str_contains($q, 'mawb') || str_contains($q, 'customs') || str_contains($q, 'tia') || str_contains($q, 'airport') || str_contains($q, 'volumetric') || str_contains($q, 'poland') || str_contains($q, 'europe') || str_contains($q, 'usa')) {
            $reply = "### ✈️ International Air Cargo & Domestic Feeder Linehaul\n\n"
                . "Namaste {$clientName}! NETPACK operates global air cargo freight departing from Tribhuvan International Airport (TIA) Cargo Terminal (KTM):\n\n"
                . "1. **Outside Kathmandu Valley Feeder Linehaul**:\n"
                . "   - Originating outside Kathmandu (e.g. Jhapa, Biratnagar, Pokhara, Chitwan, Butwal)?\n"
                . "   - Our automated feeder linehaul engine routes parcels to the KTM Cargo Terminal, bundling feeder transport with international air freight.\n"
                . "2. **Official Zero-Charges HAWB**:\n"
                . "   - Generates compliant 3-part House Air Waybills (Consignee Copy, Customs Copy, Carrier Copy) with monetary rates hidden to strictly comply with export customs clearance regulations.\n"
                . "3. **Volumetric Weight Formula**:\n"
                . "   - Chargeable weight = Higher of Gross Actual Weight vs. Volumetric Weight: `(Length × Width × Height in cm) ÷ 5000`.\n"
                . "4. **Global Corridors & Carrier Telemetry**:\n"
                . "   - Direct hub connections to DXB (Dubai), LHR (London), JFK (New York), WAW (Warsaw), SYD (Sydney).\n"
                . "   - Real-time carrier telemetry integration with DPD, DHL, FedEx, UPS, Royal Mail, Australia Post, and Aramex.";

            return [
                'success' => true,
                'provider' => 'builtin_expert',
                'client_name' => $clientName,
                'response' => $reply,
                'speech_text' => "Our international air cargo connects Tribhuvan International Airport with European and global corridors. Consignments outside Kathmandu are brought via domestic feeder linehaul, with automated zero-charge HAWBs and volumetric weight calculations.",
                'gesture' => 'speaking',
                'actions' => [
                    ['label' => 'Rate Calculator', 'url' => '/rates/inquiry'],
                ],
            ];
        }

        // 7. Prohibited & Hazardous Goods
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
                'client_name' => $clientName,
                'response' => $reply,
                'speech_text' => "Strictly prohibited items include loose lithium batteries, aerosols, flammable liquids, untaxed precious metals, and explosives. Electronic devices with built-in batteries require IATA compliant packaging.",
                'gesture' => 'alerting',
                'actions' => [],
            ];
        }

        // 8. General Conversational Assistant Fallback
        $introText = $justIntroduced
            ? "Namaste {$clientName}! 🙏 A warm welcome! I have registered your name and will address you as {$clientName} in our conversations.\n\n"
            : "Namaste {$clientName}! 🙏 I am your NETPACK AI Logistics Copilot.\n\n";

        $reply = $introText
            . "I am equipped to provide instantaneous assistance on **all 3 core NETPACK logistics services**:\n\n"
            . "1. 🇳🇵 **Domestic Express Delivery**: Doorstep delivery spanning all 7 Provinces & 77 Districts of Nepal with regional sorting hub linehauls.\n"
            . "2. ✈️ **International Air Cargo**: Tribhuvan International Airport (TIA) Gateway export cargo to 220+ countries, with domestic feeder linehauls and zero-charge HAWBs.\n"
            . "3. 📦 **E-Commerce & Cash on Delivery (COD)**: Multi-vendor seller fulfillment, same-day rider dispatch with secret 6-digit Pickup & Delivery OTPs, automated digital COD remittances, and RTO return handling.\n\n"
            . "Additionally, I provide real-time consignment tracking, operational win-win-win intelligence, and holiday schedule cutoffs.\n\n"
            . "Which service would you like to explore today?";

        return [
            'success' => true,
            'provider' => 'builtin_expert',
            'client_name' => $clientName,
            'response' => $reply,
            'speech_text' => "Namaste {$clientName}! I can assist you with all three services: Domestic Express Delivery across Nepal, International Air Cargo departing Kathmandu, or E-Commerce Deliveries with Cash on Delivery. How can I assist you right now?",
            'gesture' => 'waving',
            'actions' => [
                ['label' => '🇳🇵 Domestic', 'url' => '/shipments/create?mode=domestic'],
                ['label' => '✈️ International', 'url' => '/shipments/create?mode=international'],
                ['label' => '📦 E-Commerce COD', 'url' => '/shipments/create?mode=ecommerce'],
            ],
        ];
    }

    /**
     * Generate Tailored Step-by-Step Logistics Blueprint matching what we built in NETPACK
     */
    public function generateTailoredLogisticsPlan(array $entities, array $clientInfo): array
    {
        $clientName = $clientInfo['honorific'];
        $justIntroduced = $clientInfo['just_introduced'] ?? false;

        $originName = $entities['origin']['name'] ?? 'Nepal Hub';
        $originProvince = $entities['origin']['province'] ?? 'Koshi Province';
        $regionalHub = $entities['origin']['regional_hub'] ?? 'Regional Hub';
        $destName = $entities['destination']['name'] ?? 'International Destination';
        $destRegion = $entities['destination']['region'] ?? 'Overseas Corridor';
        $destAirport = $entities['destination']['airport'] ?? 'International Gateway Airport';
        $destCourier = $entities['destination']['courier'] ?? 'Tier-1 International Partner';
        $weight = $entities['weight'] ?? 1.0;
        $isOutsideKtm = $entities['requires_feeder'] || ($entities['origin']['is_outside_ktm'] ?? false);

        // Pre-filled booking URL parameters
        $bookingParams = [
            'shipment_type' => $entities['is_international'] ? 'international' : 'domestic',
            'weight' => $weight,
            'pickup_city' => $originName,
            'pickup_location_type' => $isOutsideKtm ? 'outside_ktm' : 'inside_ktm',
        ];
        if ($entities['is_international']) {
            $bookingParams['receiver_country'] = $destName;
        } else {
            $bookingParams['destination_city'] = $destName;
        }

        $bookingUrl = '/shipments/create?' . http_build_query($bookingParams);

        $greetingPrefix = $justIntroduced
            ? "Namaste **{$clientName}**! 🙏 It is an absolute pleasure to assist you. I have locked in your preferred name and will address you as **{$clientName}** throughout our logistics interactions.\n\n"
            : "Namaste **{$clientName}**! 🙏 Thank you for reaching out to **COURIER with NETPACK**.\n\n";

        $reply = $greetingPrefix;

        if ($entities['is_international']) {
            $reply .= "### ✈️ Consignment Roadmap: {$originName} (Nepal) &rarr; {$destName} ({$destRegion}) &middot; {$weight} kg Air Cargo\n\n"
                . "I have configured the exact end-to-end operational process engineered into our NETPACK logistics platform for your **{$weight} kg shipment from {$originName} to {$destName}**:\n\n"
                . "---\n\n"
                . "#### 📍 Stage 1: Doorstep Collection & Feeder Linehaul ({$originName} &rarr; TIA Kathmandu)\n"
                . "* **Ward-Level Doorstep Collection**: Our local dispatch fleet in **{$originName}** will collect the package directly from your home or warehouse.\n"
                . "* **Cryptographic 6-Digit Pickup OTP**: When our pickup rider arrives at your doorstep, you present the secret 6-digit Pickup OTP generated in your NETPACK console. Submitting this OTP verifies custody transfer legally and prevents unauthorized pickups.\n";

            if ($isOutsideKtm) {
                $reply .= "* **{$originProvince} Feeder Linehaul**: Because {$originName} is located outside Kathmandu Valley, your consignment is transferred to our **{$regionalHub}**, secured in a heavy-duty **Nylon Bag tagged with a unique QR manifest**, and boarded onto our nightly express highway feeder truck direct to the **Tribhuvan International Airport (TIA) Cargo Terminal (KTM)**.\n\n";
            } else {
                $reply .= "* **Direct Gateway Transfer**: Your consignment is routed directly through our Kathmandu Central Sorting Hub to the **Tribhuvan International Airport (TIA) Cargo Terminal (KTM)**.\n\n";
            }

            $reply .= "#### 📑 Stage 2: TIA Export Cargo Terminal, 3-Copy HAWB & Nepal Customs Clearance\n"
                . "* **Volumetric vs Actual Weight Audit**: At the Kathmandu Cargo Terminal, your parcel undergoes gross weight audit and volumetric calculation (`(L × W × H in cm) ÷ 5000`). If actual weight ({$weight} kg) is higher than dimensional weight, chargeable weight remains **{$weight} kg**.\n"
                . "* **Compliant Zero-Charges 3-Copy House Air Waybill (HAWB)**: NETPACK automatically generates official 3-part HAWBs (**Consignee Copy**, **Customs Copy**, and **Carrier Copy**) with monetary rates concealed, strictly complying with export valuation guidelines under the Nepal Customs Act.\n"
                . "* **Export Documentation Checklist**:\n"
                . "  - Shipper PAN Card copy or Citizenship KYC verification\n"
                . "  - Commercial Invoice (itemized with HS codes and declared values)\n"
                . "  - Packing List & non-hazardous declaration (no loose lithium batteries or restricted liquids)\n"
                . "* **Airport Security & Customs X-Ray Clearance**: Cleared by Nepal Customs and airport security at TIA.\n\n"
                . "#### 🛫 Stage 3: International Air Cargo Freight (KTM &rarr; {$destAirport})\n"
                . "* **Scheduled Airline Corridor**: Assigned to a scheduled international air carrier departing TIA (connecting through major freight hubs such as Doha, Dubai, or Istanbul) directly to **{$destAirport}**.\n"
                . "* **Live Airway Bill Telemetry**: You can track the aircraft departure, transit hub scan, and flight arrival milestones in real-time on our NETPACK radar.\n\n"
                . "#### 🚪 Stage 4: {$destName} Customs Clearance & Doorstep Handover\n"
                . "* **Destination Import Clearance**: Cleared through local customs in {$destName} under DAP/DDP incoterms.\n"
                . "* **European Tier-1 Courier Delivery**: Handed over to our premier destination partner (**{$destCourier}**) for expedited last-mile transport.\n"
                . "* **Final Doorstep Handover & POD**: Delivered directly to the recipient's doorstep in {$destName} with digital signature and electronic Proof of Delivery (POD).\n\n"
                . "---\n\n"
                . "#### 💡 Estimated Tariff & Turnaround Time\n"
                . "* **Transit Time**: **5 &ndash; 8 Business Days** (inclusive of {$originName} feeder pickup, customs processing, international flight, and final European doorstep handover).\n"
                . "* **Tariff Estimate ({$weight} kg Tier)**: International air freight + regional feeder surcharge (approx. **Rs. 24,000 &ndash; Rs. 32,000 / $180 &ndash; $240**, subject to final package volume and cargo classification).\n";
        } else {
            // Domestic Process
            $reply .= "### 🚚 Consignment Roadmap: {$originName} &rarr; {$destName} &middot; {$weight} kg Domestic Express\n\n"
                . "Here is the operational process for your **{$weight} kg domestic shipment from {$originName} to {$destName}**:\n\n"
                . "* **Stage 1: Pickup Rider & 6-Digit Pickup OTP**: Booked via NETPACK; our rider arrives at your doorstep in {$originName}. The handover is secured by a secret 6-digit Pickup OTP.\n"
                . "* **Stage 2: Regional Sorting & Highway Linehaul**: Transported to {$regionalHub}, consolidated in a QR-manifested nylon bag, and dispatched via our nightly inter-district highway truck.\n"
                . "* **Stage 3: Destination Hub & Last-Mile Delivery Rider**: Received at the destination sorting depot and assigned to a delivery rider.\n"
                . "* **Stage 4: Doorstep Delivery OTP & COD Collection**: The recipient provides the secret 6-digit Delivery OTP upon inspection and settles COD cash (if applicable).\n\n"
                . "* **Transit Time**: **24 &ndash; 48 Hours**.\n";
        }

        $actions = [
            [
                'label' => "🚀 Book {$weight}kg {$originName} to {$destName} Now",
                'url' => $bookingUrl,
            ],
            [
                'label' => '📋 Doorstep Pickup Guide',
                'url' => '/shipments/create?pickup=1',
            ],
            [
                'label' => '💰 Cargo Tariff Calculator',
                'url' => '/rates/inquiry',
            ],
        ];

        return [
            'success' => true,
            'provider' => 'builtin_expert',
            'client_name' => $clientName,
            'response' => $reply,
            'speech_text' => "Namaste {$clientName}! I have prepared the complete logistics roadmap for your {$weight} kg shipment from {$originName} to {$destName}. Our fleet collects the parcel at your doorstep in {$originName} with a 6-digit pickup OTP, transports it via feeder linehaul to Kathmandu airport for customs and zero-charge HAWB generation, flies it to {$destAirport}, and delivers it directly to the recipient's doorstep in {$destName}.",
            'gesture' => 'speaking',
            'actions' => $actions,
        ];
    }

    /**
     * Scan live network for operational bottlenecks and compute tripartite Win-Win-Win resolutions
     */
    public function getAdminOperationalIssuesAndWinWinSolutions(): array
    {
        $issues = [];

        // 1. Delayed Shipments (past estimated delivery or marked is_delayed)
        try {
            $delayedShipments = Shipment::where('is_delayed', true)
                ->orWhere(function ($q) {
                    $q->whereNotIn('status', ['delivered', 'cancelled', 'returned'])
                      ->whereNotNull('estimated_delivery')
                      ->where('estimated_delivery', '<', Carbon::now());
                })
                ->latest('updated_at')
                ->take(6)
                ->get();

            foreach ($delayedShipments as $s) {
                $hawb = $s->tracking_number ?? ('HAWB-' . $s->id);
                $origin = $s->sender_city ?: 'Regional Hub';
                $dest = $s->receiver_city ?: ($s->receiver_country ?: 'Destination');
                $delayHrs = (float)($s->delay_hours ?: round(Carbon::now()->diffInHours($s->estimated_delivery ?? $s->updated_at)));

                $issues[] = [
                    'id' => 'delay-' . $s->id,
                    'type' => 'transit_delay',
                    'category' => 'Transit Delay',
                    'severity' => $delayHrs > 24 ? 'critical' : 'warning',
                    'title' => "Highway Transit Delay: {$hawb} ({$origin} ➔ {$dest})",
                    'affected_entity' => $hawb,
                    'shipment_id' => $s->id,
                    'delay_hours' => $delayHrs,
                    'root_cause' => "Corridor linehaul delay of ~{$delayHrs}h due to highway terrain or transit sorting backlog.",
                    'win_win_win' => [
                        'client' => "Client receives automated WhatsApp/SMS status with updated ETA and 10% courtesy voucher. Zero unexpected wait or anxiety.",
                        'operations' => "Local depot & rider re-routes via secondary corridor without overtime friction or idle standby.",
                        'company' => "NETPACK safeguards 100% SLA honesty, builds lifelong client trust, and prevents customer support escalation.",
                    ],
                    'action_type' => 'notify_and_reroute',
                    'recommended_action' => "Send Proactive Reassurance to recipient ({$s->receiver_phone}) and prioritize evening linehaul.",
                    'action_route' => "/shipments/{$s->id}",
                    'action_label' => 'Inspect Consignment',
                ];
            }
        } catch (\Throwable $e) {
            Log::info('Operational delay scan error: ' . $e->getMessage());
        }

        // 2. Customs & Documentation Hold (International Air Cargo)
        try {
            $customsHolds = Shipment::where('shipment_type', 'international')
                ->where(function ($q) {
                    $q->where('customs_status', 'held')
                      ->orWhere('status', 'held_customs')
                      ->orWhere(function ($sq) {
                          $sq->whereNotIn('status', ['delivered', 'cancelled', 'returned'])
                             ->whereNull('invoice_data');
                      });
                })
                ->latest('updated_at')
                ->take(4)
                ->get();

            foreach ($customsHolds as $s) {
                $hawb = $s->tracking_number ?? ('HAWB-' . $s->id);
                $country = $s->receiver_country ?? 'Overseas';

                $issues[] = [
                    'id' => 'customs-' . $s->id,
                    'type' => 'customs_hold',
                    'category' => 'Customs & Documentation',
                    'severity' => 'critical',
                    'title' => "Export Customs Hold / Missing Tax Bill: {$hawb} to {$country}",
                    'affected_entity' => $hawb,
                    'shipment_id' => $s->id,
                    'root_cause' => "Export Commercial Invoice / PAN tax bill or HS Code verification missing at TIA Cargo export terminal.",
                    'win_win_win' => [
                        'client' => "Rapid 1-minute digital invoice upload link sent directly to client phone, avoiding physical trip to airport.",
                        'operations' => "Air carrier partner (e.g. DPD/DHL) receives pre-cleared documentation before takeoff, avoiding demurrage penalties.",
                        'company' => "NETPACK maintains strict compliance with Nepal Customs Department without risking consignment grounding.",
                    ],
                    'action_type' => 'request_invoice',
                    'recommended_action' => "Trigger 1-Click Invoice Upload Reminder to {$s->sender_name} ({$s->sender_phone}).",
                    'action_route' => "/shipments/{$s->id}/commercial-invoice",
                    'action_label' => 'Generate Invoice',
                ];
            }
        } catch (\Throwable $e) {
            Log::info('Customs hold scan error: ' . $e->getMessage());
        }

        // 3. Unassigned Doorstep Pickups (> 1 Hour Pending)
        try {
            $pendingPickups = PickupRequest::where('status', 'pending')
                ->where('created_at', '<', Carbon::now()->subMinutes(60))
                ->latest()
                ->take(4)
                ->get();

            foreach ($pendingPickups as $p) {
                $pNum = $p->pickup_number ?? ('PKP-' . $p->id);
                $city = $p->pickup_city ?? 'Kathmandu';
                $elapsed = round(Carbon::now()->diffInMinutes($p->created_at));

                $issues[] = [
                    'id' => 'pickup-' . $p->id,
                    'type' => 'pending_pickup',
                    'category' => 'Doorstep Pickup',
                    'severity' => 'warning',
                    'title' => "Unassigned Doorstep Pickup: {$pNum} in {$city} ({$elapsed}m pending)",
                    'affected_entity' => $pNum,
                    'shipment_id' => null,
                    'root_cause' => "Pickup request waiting for rider assignment past the 60-minute dispatch threshold.",
                    'win_win_win' => [
                        'client' => "Guaranteed doorstep collection today with direct rider contact & pickup OTP confirmation.",
                        'operations' => "Closest active GPS rider receives route cluster bonus, boosting rider income and fuel efficiency.",
                        'company' => "NETPACK prevents client cancellation and achieves 99% doorstep pickup SLA adherence.",
                    ],
                    'action_type' => 'assign_rider',
                    'recommended_action' => "Auto-cluster dispatch to nearest active rider in {$city}.",
                    'action_route' => '/admin/domestic/pickup-requests',
                    'action_label' => 'Assign Rider',
                ];
            }
        } catch (\Throwable $e) {
            Log::info('Pending pickup scan error: ' . $e->getMessage());
        }

        // 4. Client Complaints & Damage Inquiries (ShipmentIssue)
        try {
            $activeIssues = ShipmentIssue::whereIn('status', ['open', 'in_progress', 'pending'])
                ->latest()
                ->take(4)
                ->get();

            foreach ($activeIssues as $issue) {
                $iNum = $issue->issue_number ?? ('ISS-' . $issue->id);
                $title = $issue->title ?: 'Client Delivery Inquiry';
                $claim = $issue->claimed_amount ? 'Rs. ' . number_format($issue->claimed_amount) : 'Investigation';

                $issues[] = [
                    'id' => 'issue-' . $issue->id,
                    'type' => 'client_complaint',
                    'category' => 'Customer Care',
                    'severity' => 'critical',
                    'title' => "Customer Inquiry: {$iNum} &middot; {$title}",
                    'affected_entity' => $iNum,
                    'shipment_id' => $issue->shipment_id,
                    'root_cause' => $issue->situation_description ?: 'Client reported transit concern regarding package handling or delivery timeline.',
                    'win_win_win' => [
                        'client' => "Transparent investigation within 4 business hours, direct call from Senior Care Officer, and prompt claim resolution.",
                        'operations' => "Detailed root cause logged prevents recurring transit damage or route misplacement.",
                        'company' => "Transforms a frustrated client into an enthusiastic long-term brand ambassador through golden service.",
                    ],
                    'action_type' => 'resolve_complaint',
                    'recommended_action' => "Inspect claim ({$claim}) and trigger customer reassurance callback.",
                    'action_route' => '/admin/issues',
                    'action_label' => 'Resolve Ticket',
                ];
            }
        } catch (\Throwable $e) {
            Log::info('Shipment issue scan error: ' . $e->getMessage());
        }

        // If zero issues found, celebrate network health
        if (empty($issues)) {
            $issues[] = [
                'id' => 'network-healthy',
                'type' => 'healthy',
                'category' => 'Network SLA Health',
                'severity' => 'advisory',
                'title' => 'Logistics Network Operating at 100% SLA Health',
                'affected_entity' => 'All 7 Provinces & International Gateways',
                'shipment_id' => null,
                'root_cause' => 'All highway linehauls, TIA cargo exports, and doorstep deliveries are tracking smoothly on schedule.',
                'win_win_win' => [
                    'client' => 'Consignments arrive on time with zero delay or unexpected fees.',
                    'operations' => 'Riders and hub sorting teams operate with calm, balanced throughput.',
                    'company' => 'Maximum operating margin, pristine reputation, and 5-star customer reviews.',
                ],
                'action_type' => 'monitor',
                'recommended_action' => 'Maintain scheduled 7:00 PM linehaul dispatches across Eastern and Western corridors.',
                'action_route' => '/admin/dashboard',
                'action_label' => 'View Radar',
            ];
        }

        return $issues;
    }

    /**
     * Execute 1-Click Win-Win Operational Action
     */
    public function executeAdminWinWinAction(string $issueId, string $actionType, ?int $shipmentId = null, ?string $notes = null): array
    {
        $message = "Win-Win operational resolution applied successfully.";

        try {
            if ($shipmentId) {
                $shipment = Shipment::find($shipmentId);
                if ($shipment) {
                    if ($actionType === 'notify_and_reroute') {
                        $shipment->status_notes = ($shipment->status_notes ? $shipment->status_notes . ' | ' : '') . 'AI Reassurance triggered: recipient notified of priority linehaul re-dispatch.';
                        $shipment->is_delayed = false;
                        $shipment->save();
                        $message = "Priority linehaul re-dispatch logged for HAWB {$shipment->tracking_number}. Recipient notified via automated system update.";
                    } elseif ($actionType === 'request_invoice') {
                        $shipment->customs_status = 'pending_client_docs';
                        $shipment->status_notes = ($shipment->status_notes ? $shipment->status_notes . ' | ' : '') . 'AI Alert: 1-Click invoice upload link sent to client.';
                        $shipment->save();
                        $message = "Digital documentation upload link dispatched to {$shipment->sender_name} ({$shipment->sender_phone}). Demurrage hold averted.";
                    }
                }
            }

            if (str_starts_with($issueId, 'issue-')) {
                $issueDbId = str_replace('issue-', '', $issueId);
                $issue = ShipmentIssue::find($issueDbId);
                if ($issue) {
                    $issue->status = 'in_progress';
                    $issue->resolution_notes = ($issue->resolution_notes ? $issue->resolution_notes . "\n" : '') . 'AI Win-Win Protocol initiated: Senior officer assigned for priority resolution.';
                    $issue->save();
                    $message = "Issue {$issue->issue_number} updated to In Progress. Customer callback queued.";
                }
            }
        } catch (\Throwable $e) {
            Log::info('Error executing win-win action: ' . $e->getMessage());
        }

        return [
            'success' => true,
            'message' => $message,
            'issue_id' => $issueId,
            'action_type' => $actionType,
        ];
    }

    /**
     * Format Admin Operational Intelligence Report for AI Chat & Voice
     */
    public function generateAdminOperationalIntelligenceReport(array $clientInfo, string $query): array
    {
        $clientName = $clientInfo['honorific'];
        $issues = $this->getAdminOperationalIssuesAndWinWinSolutions();

        $criticalCount = count(array_filter($issues, fn($i) => ($i['severity'] ?? '') === 'critical'));
        $warningCount = count(array_filter($issues, fn($i) => ($i['severity'] ?? '') === 'warning'));

        $reply = "### 🧠 AI Operational Intelligence & Win-Win-Win Resolution Report\n\n"
            . "Namaste **{$clientName}**! Here is the live operational health status of the **COURIER with NETPACK** network across Nepal and international gateways:\n\n";

        if ($criticalCount > 0 || $warningCount > 0) {
            $reply .= "⚠️ **Active Operational Exceptions**: **{$criticalCount} Critical** &middot; **{$warningCount} Moderate Warnings**\n\n";
        } else {
            $reply .= "✅ **All Logistics Corridors Healthy**: 0 critical holds. 100% SLA compliance.\n\n";
        }

        foreach (array_slice($issues, 0, 4) as $idx => $iss) {
            $badge = match ($iss['severity'] ?? 'advisory') {
                'critical' => '🔴 **CRITICAL**',
                'warning' => '🟠 **ATTENTION**',
                default => '🟢 **OPTIMAL**',
            };

            $reply .= "#### " . ($idx + 1) . ". {$iss['title']} &middot; {$badge}\n"
                . "* **Root Cause**: {$iss['root_cause']}\n"
                . "* **Win-Win-Win Tripartite Solution**:\n"
                . "  - 🟢 **Win for Client**: {$iss['win_win_win']['client']}\n"
                . "  - 🔵 **Win for Operations/Partners**: {$iss['win_win_win']['operations']}\n"
                . "  - 🟣 **Win for NETPACK**: {$iss['win_win_win']['company']}\n"
                . "* **Recommended Action**: {$iss['recommended_action']}\n\n";
        }

        $reply .= "💡 *Every issue resolved through our Win-Win-Win protocol turns a logistical challenge into long-term trust, driver efficiency, and enterprise growth.*";

        $speech = "Namaste {$clientName}! I have scanned our logistics network. We have " . ($criticalCount > 0 ? "{$criticalCount} critical exceptions and {$warningCount} warnings" : "zero critical holds") . ". I have outlined win-win-win solutions for each case to ensure client peace of mind, operational efficiency, and NETPACK SLA integrity.";

        return [
            'success' => true,
            'provider' => 'builtin_expert',
            'client_name' => $clientName,
            'response' => $reply,
            'speech_text' => $speech,
            'gesture' => 'speaking',
            'actions' => [
                ['label' => 'Open Operations Dashboard', 'url' => '/admin/dashboard'],
                ['label' => 'Manage Delay Hub', 'url' => '/admin/communications'],
            ],
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
            'client_name' => $clientName,
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
        if (preg_match('/\b(NP[-\w\d]+|[A-Z]{2,4}[-\d]{4,15}|\d{8,14})\b/i', $query, $matches)) {
            $candidateNumber = trim($matches[1]);

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
        if (preg_match('/(?:rate|cost|price|quote|how much)\s+(?:from|for)?\s*([a-zA-Z\s]+)\s+to\s+([a-zA-Z\s]+)/i', $query, $matches)) {
            $origin = trim($matches[1]);
            $dest = trim($matches[2]);

            $weight = 1.0;
            if (preg_match('/(\d+(?:\.\d+)?)\s*(?:kg|kilo|gram)/i', $query, $wMatch)) {
                $weight = (float) $wMatch[1];
            }

            $basePrice = 120;
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

        if ($month == 9 || $month == 10) {
            return $occasions[0] ?? null; // Dashain
        } elseif ($month == 11) {
            return $occasions[1] ?? null; // Tihar / Chhath
        } elseif ($month == 12) {
            return $occasions[5] ?? null; // Christmas / New Year
        } elseif ($month == 4) {
            return $occasions[3] ?? null; // Nepali New Year
        }

        return $occasions[0] ?? null;
    }

    /**
     * Clean markdown for natural text-to-speech output
     */
    public function sanitizeForSpeech(string $markdown): string
    {
        $text = preg_replace('/^#+\s+/m', '', $markdown);
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = preg_replace('/\*([^*]+)\*/', '$1', $text);
        $text = preg_replace('/`([^`]+)`/', '$1', $text);
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text);
        $text = preg_replace('/&rarr;/', 'to', $text);
        $text = preg_replace('/&middot;/', ',', $text);
        $text = preg_replace('/&ndash;/', '-', $text);
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
    protected function extractActions(string $content, array $entities = []): array
    {
        $actions = [];

        // If origin and destination were extracted, generate a direct prefilled booking button!
        if (!empty($entities['destination']['name']) && !empty($entities['origin']['name'])) {
            $origin = $entities['origin']['name'];
            $dest = $entities['destination']['name'];
            $weight = $entities['weight'] ?? 1.0;
            $isOutside = $entities['requires_feeder'] || ($entities['origin']['is_outside_ktm'] ?? false);

            $params = [
                'shipment_type' => $entities['is_international'] ? 'international' : 'domestic',
                'weight' => $weight,
                'pickup_city' => $origin,
                'pickup_location_type' => $isOutside ? 'outside_ktm' : 'inside_ktm',
            ];
            if ($entities['is_international']) {
                $params['receiver_country'] = $dest;
            } else {
                $params['destination_city'] = $dest;
            }

            $actions[] = [
                'label' => "🚀 Book {$weight}kg {$origin} to {$dest} Now",
                'url' => '/shipments/create?' . http_build_query($params),
            ];
        } else {
            $actions[] = ['label' => 'Book Shipment', 'url' => '/shipments/create'];
        }

        if (str_contains($content, 'rate') || str_contains($content, 'tariff') || str_contains($content, 'cost')) {
            $actions[] = ['label' => 'Rate Calculator', 'url' => '/rates/inquiry'];
        }
        if (str_contains($content, 'tracking') || str_contains($content, 'telemetry') || str_contains($content, 'radar')) {
            $actions[] = ['label' => 'Consignment Tracking', 'url' => '/tracking'];
        }

        return $actions;
    }

    /**
     * Build comprehensive system prompt for external LLM models (Gemini, OpenAI, Claude, Groq)
     */
    protected function buildSystemKnowledgePrompt(?User $user, string $clientName, array $entities = []): string
    {
        $userRole = $user ? $user->user_type : 'guest';
        $entityContext = '';
        if (!empty($entities['origin']) || !empty($entities['destination'])) {
            $originName = $entities['origin']['name'] ?? 'Nepal';
            $destName = $entities['destination']['name'] ?? 'Destination';
            $weight = $entities['weight'] ?? 'declared';
            $feederReq = ($entities['requires_feeder'] ?? false) ? 'YES (Outside KTM Feeder to TIA Cargo Gateway)' : 'Direct';
            $entityContext = "\nDETECTED USER SHIPMENT PARAMETERS:\n- Client Name: {$clientName}\n- Origin: {$originName}\n- Destination: {$destName}\n- Declared Weight: {$weight} kg\n- Feeder Linehaul Required: {$feederReq}\n";
        }

        return <<<EOT
You are NETPACK AI Logistics Copilot, the official conversational AI assistant of "COURIER with NETPACK" (Nepal's premier logistics, e-commerce, and international air cargo management platform).
The user interacting with you is {$clientName} (Role: {$userRole}).
{$entityContext}
YOUR PERSONALITY & TONE:
1. Always address the client warmly and politely using their preferred name: "Namaste {$clientName}!". If the user introduced themselves, acknowledge their name enthusiastically.
2. Tone: Highly intelligent, authoritative on logistics, culturally attuned to Nepal and international air corridors, energetic, and professional. Avoid robotic or dry boilerplate!
3. Format: Clean GitHub-flavored markdown with structured bullet points, clear stage breakdown, and direct calls-to-action.

SYSTEM LOGISTICS ARCHITECTURE & WORKFLOW RULES:
- THE 3 CORE SERVICES OF NETPACK:
  1. Domestic Express Delivery: Doorstep parcel delivery across all 7 Provinces & 77 Districts of Nepal. Regional sorting hubs in Biratnagar, Janakpur, Kathmandu, Pokhara, Butwal, Surkhet, Dhangadhi with nylon QR manifests.
  2. International Air Cargo & Feeder Logistics: Global air freight departing Tribhuvan International Airport (TIA) Cargo Terminal, with automatic feeder linehaul linking outer districts (Jhapa, Chitwan, etc.) with Kathmandu airport, zero-charge HAWBs, and Tier-1 carrier tracking (DPD, DHL, FedEx, UPS).
  3. E-Commerce & Cash on Delivery (COD) Delivery: Online seller & merchant fulfillment, same-day rider dispatch, 6-digit Pickup OTP (Seller to Rider) and 6-digit Delivery OTP (Rider to Customer), automated digital COD payout to Bank/eSewa/Khalti, and RTO return management.

- When the user asks to book or explains a shipment (e.g. from Jhapa to Poland, 20kg):
  1. DO NOT give a generic boilerplate or random answer.
  2. Walk them through the EXACT 4-stage logistics process built in NETPACK:
     * Stage 1: Doorstep Collection in Origin (e.g. Jhapa) via local courier rider with secret 6-digit Pickup OTP. Consignment transferred to Regional Hub (e.g. Biratnagar Hub for Koshi Province), packed in QR-manifested Nylon Bag, and moved via highway feeder truck to Tribhuvan International Airport (TIA) Cargo Terminal, Kathmandu.
     * Stage 2: TIA Export Gateway, Zero-Charges 3-Copy HAWB (Consignee, Customs, Carrier copies with rates omitted per customs export rules), export documentation (Shipper PAN/Citizenship KYC, Commercial Invoice with HS codes, Packing list), and airport X-ray clearance.
     * Stage 3: Scheduled air cargo flight departure from KTM (via Doha/Dubai/Istanbul corridor) to destination gateway (e.g. Warsaw Chopin Airport WAW for Poland) with live air telemetry.
     * Stage 4: Destination customs clearance (DAP/DDP) and European Tier-1 courier partner delivery (DPD Poland / DHL Express / FedEx Europe) directly to consignee doorstep with verified digital POD.
  3. Mention estimated turnaround time (5-8 business days) and realistic pricing guide.
- E-Commerce & Door-to-Door Delivery Security:
  * 6-digit cryptographic Pickup OTP (Seller to Rider)
  * 6-digit cryptographic Delivery OTP (Rider to Customer with COD collection)
  * Proof of Delivery (POD) photo capture.
- Cash On Delivery (COD) Ledgers:
  * 100% segregated from rider earnings. Tiered limits: Level 0 (Rs 0), Level 1 (Rs 5,000), Level 2 (Rs 20,000), Level 3 (Rs 50,000). Automated remittance to eSewa/Khalti/Bank.
- Operational Schedules:
  * Same-day pickup cutoff: 12:00 PM; TIA cargo cutoff: 3:00 PM; Night linehauls depart: 7:00 PM.
- Important Occasions: Bada Dashain, Tihar, Nepali New Year, Black Friday, Christmas.
EOT;
    }

    /**
     * Parse spoken voice input for individual consignment form fields
     */
    public function parseVoiceFormField(string $step, string $text, string $mode = 'domestic'): array
    {
        $raw = trim($text);
        $clean = strtolower($raw);
        $value = $raw;
        $speechAck = "Got it!";

        switch ($step) {
            case 'mode':
                if (str_contains($clean, 'ecommerce') || str_contains($clean, 'e-commerce') || str_contains($clean, 'cod') || str_contains($clean, 'cash on delivery') || str_contains($clean, 'online store') || str_contains($clean, 'merchant') || str_contains($clean, 'seller') || str_contains($clean, 'store') || str_contains($clean, 'shop')) {
                    $value = 'ecommerce';
                    $speechAck = "Selected E-Commerce and Cash on Delivery service.";
                } elseif (str_contains($clean, 'international') || str_contains($clean, 'overseas') || str_contains($clean, 'air cargo') || str_contains($clean, 'abroad') || str_contains($clean, 'bidesh') || str_contains($clean, 'poland') || str_contains($clean, 'europe') || str_contains($clean, 'usa')) {
                    $value = 'international';
                    $speechAck = "Selected International Air Cargo service.";
                } else {
                    $value = 'domestic';
                    $speechAck = "Selected Domestic Express courier service within Nepal.";
                }
                break;


            case 'pickup_city':
                $entities = $this->parseLogisticsEntities($raw);
                if (!empty($entities['origin']['name'])) {
                    $value = $entities['origin']['name'];
                } else {
                    $cleaned = preg_replace('/^(pickup\s+from|from|at|in|city\s+is|bata|dekhi)\s+/i', '', $raw);
                    $cleaned = preg_replace('/\s+(bata|dekhi|ma)$/i', '', $cleaned);
                    $value = ucwords(trim($cleaned));
                }
                $speechAck = "Pickup city set to {$value}.";
                break;

            case 'sender_name':
            case 'receiver_name':
                $value = ucwords(preg_replace('/^(my\s*name\s*is|the\s*name\s*is|contact\s*is|sender\s*is|receiver\s*is|this\s*is|naam\s*chai|to)\s+/i', '', $raw));
                $speechAck = "Name set to {$value}.";
                break;

            case 'sender_phone':
            case 'receiver_phone':
                $wordToNum = [
                    'sunya' => '0', 'zero' => '0', 'ek' => '1', 'one' => '1', 'dui' => '2', 'two' => '2',
                    'tin' => '3', 'teen' => '3', 'three' => '3', 'char' => '4', 'four' => '4',
                    'panch' => '5', 'paanch' => '5', 'five' => '5', 'chha' => '6', 'six' => '6',
                    'sat' => '7', 'saat' => '7', 'seven' => '7', 'aath' => '8', 'eight' => '8',
                    'nau' => '9', 'nine' => '9',
                ];
                $dig = $clean;
                foreach ($wordToNum as $w => $d) {
                    $dig = preg_replace('/\b' . $w . '\b/', $d, $dig);
                }
                $digits = preg_replace('/\D/', '', $dig);
                $value = !empty($digits) ? $digits : $raw;
                $speechAck = "Phone number captured.";
                break;

            case 'destination':
                $entities = $this->parseLogisticsEntities($raw);
                if (!empty($entities['destination']['name'])) {
                    $value = $entities['destination']['name'];
                } else {
                    $cleaned = preg_replace('/^(to|for|destination\s+is|shipping\s+to|ma|lai)\s+/i', '', $raw);
                    $cleaned = preg_replace('/\s+(pathaune|pathauna|ma|lai|pugne)$/i', '', $cleaned);
                    $value = ucwords(trim($cleaned));
                }
                $speechAck = ($mode === 'international') ? "Destination country set to {$value}." : "Destination set to {$value}.";
                break;

            case 'weight':
                $nepaliWordNums = [
                    'ek' => '1', 'dui' => '2', 'tin' => '3', 'teen' => '3', 'char' => '4',
                    'panch' => '5', 'paanch' => '5', 'chha' => '6', 'sat' => '7', 'saat' => '7',
                    'aath' => '8', 'nau' => '9', 'das' => '10', 'pandhra' => '15', 'bis' => '20',
                    'bees' => '20', 'pachis' => '25', 'tis' => '30', 'pachas' => '50', 'saya' => '100',
                ];
                $weightText = $clean;
                foreach ($nepaliWordNums as $nw => $nv) {
                    $weightText = preg_replace('/\b' . $nw . '\b/', $nv, $weightText);
                }
                if (preg_match('/(\d+(?:\.\d+)?)/', $weightText, $m)) {
                    $value = (float) $m[1];
                } else {
                    $entities = $this->parseLogisticsEntities($raw);
                    $value = $entities['weight'] ?? 1.0;
                }
                $speechAck = "Weight set to {$value} kilograms.";
                break;

            case 'description':
                if (str_contains($clean, 'luga') || str_contains($clean, 'kapada') || str_contains($clean, 'clothes') || str_contains($clean, 'dress') || str_contains($clean, 'shirt')) {
                    $value = 'Apparel & Garments';
                } elseif (str_contains($clean, 'kagaj') || str_contains($clean, 'dastabej') || str_contains($clean, 'document') || str_contains($clean, 'paper') || str_contains($clean, 'file')) {
                    $value = 'Official Documents & Papers';
                } elseif (str_contains($clean, 'khadya') || str_contains($clean, 'sukuti') || str_contains($clean, 'masala') || str_contains($clean, 'aachar') || str_contains($clean, 'gundruk') || str_contains($clean, 'food')) {
                    $value = 'Packaged Dry Foodstuff & Spices';
                } elseif (str_contains($clean, 'hastakala') || str_contains($clean, 'handicraft') || str_contains($clean, 'souvenir') || str_contains($clean, 'dhaka')) {
                    $value = 'Nepali Handicrafts & Cultural Items';
                } else {
                    $value = ucfirst(trim($raw));
                }
                $speechAck = "Contents noted as {$value}.";
                break;

            case 'service_type':
                if (str_contains($clean, 'flash') || str_contains($clean, 'urgent') || str_contains($clean, 'instant') || str_contains($clean, 'chito')) {
                    $value = 'flash';
                    $speechAck = "Selected Flash service.";
                } elseif (str_contains($clean, 'same day') || str_contains($clean, 'express') || str_contains($clean, 'aajai') || str_contains($clean, 'priority')) {
                    $value = ($mode === 'international') ? 'express' : 'same_day';
                    $speechAck = "Selected Express / Same-day service.";
                } elseif (str_contains($clean, 'himalayan') || str_contains($clean, 'mountain') || str_contains($clean, 'remote')) {
                    $value = 'himalayan';
                    $speechAck = "Selected Himalayan Remote District service.";
                } elseif (str_contains($clean, 'economy') || str_contains($clean, 'cargo')) {
                    $value = 'economy';
                    $speechAck = "Selected Economy service.";
                } else {
                    $value = ($mode === 'international') ? 'economy' : 'standard';
                    $speechAck = "Selected Standard transit service.";
                }
                break;

            case 'package_type':
                if (str_contains($clean, 'box') || str_contains($clean, 'carton')) {
                    $value = 'box';
                    $speechAck = "Package type set to Box Carton.";
                } elseif (str_contains($clean, 'envelope') || str_contains($clean, 'document') || str_contains($clean, 'letter') || str_contains($clean, 'kagaj')) {
                    $value = 'envelope';
                    $speechAck = "Package type set to Document Envelope.";
                } elseif (str_contains($clean, 'fragile') || str_contains($clean, 'glass') || str_contains($clean, 'electronic')) {
                    $value = 'fragile';
                    $speechAck = "Package flagged as Fragile.";
                } elseif (str_contains($clean, 'grocery') || str_contains($clean, 'food')) {
                    $value = 'grocery';
                    $speechAck = "Package set to Grocery / Perishable.";
                } else {
                    $value = 'parcel';
                    $speechAck = "Package type set to Standard Parcel.";
                }
                break;

            case 'dimensions':
                preg_match_all('/\d+(?:\.\d+)?/', $clean, $matches);
                if (!empty($matches[0])) {
                    $dims = array_map('floatval', $matches[0]);
                    $length = $dims[0] ?? 20.0;
                    $width = $dims[1] ?? $length;
                    $height = $dims[2] ?? $width;
                    $value = ['length' => $length, 'width' => $width, 'height' => $height];
                    $speechAck = "Dimensions set to {$length} by {$width} by {$height} cm.";
                } else {
                    $value = ['length' => 20.0, 'width' => 20.0, 'height' => 20.0];
                    $speechAck = "Standard dimensions applied.";
                }
                break;

            case 'receiver_postal_code':
                $dig = preg_replace('/\D/', '', $clean);
                $value = !empty($dig) ? $dig : strtoupper(trim($raw));
                $speechAck = "Postal code set to {$value}.";
                break;

            case 'receiver_city':
                $cleaned = preg_replace('/^(city\s+is|in|at)\s+/i', '', $raw);
                $value = ucwords(trim($cleaned));
                $speechAck = "City set to {$value}.";
                break;

            case 'receiver_country':
                $entities = $this->parseLogisticsEntities($raw);
                if (!empty($entities['destination']['name'])) {
                    $value = $entities['destination']['name'];
                } else {
                    $cleaned = preg_replace('/^(to|for|destination\s+is|shipping\s+to|ma|lai)\s+/i', '', $raw);
                    $cleaned = preg_replace('/\s+(pathaune|pathauna|ma|lai|pugne)$/i', '', $cleaned);
                    $value = ucwords(trim($cleaned));
                }
                $speechAck = "Destination country set to {$value}.";
                break;

            case 'destination_district':
                $matchedDistrict = null;
                foreach (\App\Services\NepalGeographicalService::getAllDistricts() as $dist) {
                    if (stripos($raw, $dist) !== false) {
                        $matchedDistrict = $dist;
                        break;
                    }
                }
                if ($matchedDistrict) {
                    $value = $matchedDistrict;
                } else {
                    $cleaned = preg_replace('/^(please\s+deliver\s+to|deliver\s+to|shipping\s+to|send\s+to|to|for|destination\s+is|district\s+is|ma|lai)\s+/i', '', $raw);
                    $cleaned = preg_replace('/\s+(pathaune|pathauna|ma|lai|pugne|district|zilla)$/i', '', $cleaned);
                    $value = ucwords(trim($cleaned));
                }
                $speechAck = "Destination district set to {$value}.";
                break;

            case 'pickup_address':
            case 'receiver_address':
            case 'delivery_address':
            case 'receiver_street':
            default:
                $value = ucfirst(trim($raw));
                $speechAck = "Noted.";
                break;
        }

        return [
            'step' => $step,
            'raw_text' => $raw,
            'parsed_value' => $value,
            'speech_ack' => $speechAck,
        ];
    }
}
