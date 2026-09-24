<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Assistant Provider & Core Engine
    |--------------------------------------------------------------------------
    |
    | Supported providers: "builtin" (zero API dependency, local expert engine),
    | "openai" (GPT-4o, GPT-5.6, Whisper, Realtime), "gemini" (Google Gemini),
    | "claude" (Anthropic Claude).
    |
    */
    'default_provider' => env('AI_PROVIDER', 'builtin'),

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'project_id' => env('OPENAI_PROJECT_ID'),
            'model' => env('OPENAI_TEXT_MODEL', 'gpt-4o'),
            'realtime_model' => env('OPENAI_REALTIME_MODEL', 'gpt-realtime-2.1'),
            'voice_model' => env('OPENAI_TTS_MODEL', 'tts-1'),
            'voice' => env('OPENAI_VOICE', 'alloy'), // alloy, echo, fable, onyx, nova, shimmer
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        ],
        'claude' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
        ],
        'elevenlabs' => [
            'api_key' => env('ELEVENLABS_API_KEY'),
            'voice_id' => env('ELEVENLABS_VOICE_ID', '21m00Tcm4TlvDq8ikWAM'), // Rachel / custom
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Voice & Speech Configuration
    |--------------------------------------------------------------------------
    */
    'voice' => [
        'enabled' => true,
        'synthesis_provider' => env('AI_TTS_PROVIDER', 'browser'), // 'browser', 'openai', 'elevenlabs'
        'recognition_provider' => env('AI_STT_PROVIDER', 'browser'), // 'browser', 'openai_whisper'
        'default_language' => 'en-US',
        'nepali_accent_support' => true,
        'speech_rate' => 1.0,
        'speech_pitch' => 1.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Operational Cutoff Times & Logistics Service Schedules (Nepal Time UTC+5:45)
    |--------------------------------------------------------------------------
    */
    'schedules' => [
        'same_day_cutoff' => '12:00', // Book by 12:00 PM for same-day delivery
        'express_intacity_hours' => '2-4 hours',
        'tia_cargo_intake_cutoff' => '15:00', // 3:00 PM for night/next-day international flights
        'night_linehaul_departure' => '19:00', // 7:00 PM highway departure from Kathmandu Central Hub
        'depot_operating_hours' => '08:00 - 20:00',
        'weekend_policy' => '6 Days active (Sunday to Friday full dispatch; Saturday urgent express only)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Major Occasions, Dates & Festival Logistics Peak Calendar
    |--------------------------------------------------------------------------
    */
    'occasions' => [
        [
            'id' => 'dashain',
            'name' => 'Bada Dashain (Bijaya Dashami)',
            'approx_month' => 'September - October',
            'season' => 'Autumn',
            'impact_level' => 'CRITICAL_PEAK',
            'description' => 'Nepal’s biggest national festival. Massive surge in home deliveries, new clothes, electronics, gifts to 77 districts.',
            'logistical_notice' => 'Highway linehauls experience severe traffic. Consignments to Karnali and Sudurpashchim should be dispatched at least 5-7 days before Fulpati.',
            'cutoff_days_prior' => 5,
            'greetings' => [
                'Bada Dashain ko mangalmaya subhakamana! 🌾 May Goddess Durga bless you and your family!',
                'Happy Dashain preparations! 🪁 Ensure all remote district gift parcels are booked early to avoid festive highway rush!',
                'Dashain Subhakamana! 🌾 Our door-to-door delivery fleet is working around the clock for your festive consignments.',
            ],
        ],
        [
            'id' => 'tihar',
            'name' => 'Tihar / Deepawali & Bhai Tika',
            'approx_month' => 'October - November',
            'season' => 'Autumn',
            'impact_level' => 'HIGH_PEAK',
            'description' => 'Festival of Lights & Brotherhood. Enormous volume of Bhai Masala (dry fruits), sweets, jewelry, and international courier to diaspora.',
            'logistical_notice' => 'Perishable sweets require express flash dispatch. International air courier cutoffs for USA, UK, and Australia are strictly 7 days before Bhai Tika.',
            'cutoff_days_prior' => 4,
            'greetings' => [
                'Deepawali & Bhai Tika ko hardik subhakamana! 🪔 May your life be illuminated with joy and prosperity!',
                'Happy Tihar! 🪔 Sending Bhai Masala or gifts? Book our expedited courier for guaranteed doorstep arrival before Bhai Tika.',
            ],
        ],
        [
            'id' => 'chhath',
            'name' => 'Chhath Puja',
            'approx_month' => 'November',
            'season' => 'Early Winter',
            'impact_level' => 'REGIONAL_PEAK',
            'description' => 'Sacred sun worship festival across Madhesh and Koshi provinces (Janakpur, Birgunj, Biratnagar).',
            'logistical_notice' => 'Regional sorting hubs in Janakpur and Birgunj observe custom holiday timings; book early for Terai deliveries.',
            'cutoff_days_prior' => 3,
            'greetings' => [
                'Chhath Parva ke dherai dherai subhakamana! 🌅 Warm blessings on this holy occasion.',
            ],
        ],
        [
            'id' => 'nepali_new_year',
            'name' => 'Nepali New Year (Baisakh 1)',
            'approx_month' => 'Mid April',
            'season' => 'Spring',
            'impact_level' => 'HIGH_PEAK',
            'description' => 'Start of the Bikram Sambat new fiscal and cultural year. Commercial promotions, retail inventory shipments, corporate gifting.',
            'logistical_notice' => 'High inter-city demand. Advance pickup booking advised for Kathmandu, Pokhara, and Chitwan.',
            'cutoff_days_prior' => 2,
            'greetings' => [
                'Naya Barsha ko hardik mangalmaya subhakamana! 🌸 Wishing you boundless success and seamless logistics this new year.',
            ],
        ],
        [
            'id' => 'black_friday_cyber_monday',
            'name' => 'Global Black Friday & Cyber Monday Peak',
            'approx_month' => 'Late November',
            'season' => 'Winter',
            'impact_level' => 'INTERNATIONAL_PEAK',
            'description' => 'Massive global e-commerce import/export rush connecting KTM with Dubai, London, New York, and Sydney corridors.',
            'logistical_notice' => 'International air cargo space is constrained; ensure all export HAWBs and customs invoices are pre-cleared.',
            'cutoff_days_prior' => 3,
            'greetings' => [
                'Welcome to peak global shipping season! ✈️ Take advantage of our consolidated air cargo tariffs to USA, Europe, and Australia.',
            ],
        ],
        [
            'id' => 'christmas_new_year',
            'name' => 'Christmas & International New Year Cargo Rush',
            'approx_month' => 'December',
            'season' => 'Winter',
            'impact_level' => 'INTERNATIONAL_PEAK',
            'description' => 'Global holiday dispatch season with Tribhuvan International Airport cargo congestion.',
            'logistical_notice' => 'Book air freight consignments at least 10 days before Christmas to guarantee overseas delivery before holiday airport shutdowns.',
            'cutoff_days_prior' => 7,
            'greetings' => [
                'Merry Christmas & Happy Holiday Season! 🎄 Ensure your international air cargo is dispatched before our airline cutoff dates.',
            ],
        ],
        [
            'id' => 'buddha_jayanti',
            'name' => 'Buddha Jayanti',
            'approx_month' => 'May',
            'season' => 'Summer',
            'impact_level' => 'NORMAL',
            'description' => 'Celebration of Lord Buddha’s birth at Lumbini.',
            'logistical_notice' => 'Normal operations with minor holiday hub adjustments in Lumbini Province.',
            'cutoff_days_prior' => 1,
            'greetings' => [
                'Buddha Jayanti ko hardik subhakamana! 🕊️ Peace, happiness, and harmonious deliveries to you.',
            ],
        ],
        [
            'id' => 'holi',
            'name' => 'Holi / Fagu Purnima',
            'approx_month' => 'March',
            'season' => 'Spring',
            'impact_level' => 'MODERATE',
            'description' => 'Festival of Colors. Hilly region on Day 1, Terai region on Day 2.',
            'logistical_notice' => 'Last-mile door delivery paused during color play hours (10 AM - 4 PM) for rider safety; resumes in evening.',
            'cutoff_days_prior' => 2,
            'greetings' => [
                'Happy Holi! 🎨 Wishing you a vibrant, joyful, and prosperous celebration!',
            ],
        ],
        [
            'id' => 'teej',
            'name' => 'Haritalika Teej',
            'approx_month' => 'August - September',
            'season' => 'Monsoon',
            'impact_level' => 'MODERATE',
            'description' => 'Celebration of women across Nepal. High volume of traditional sarees, jewelry, and gift box dispatches.',
            'logistical_notice' => 'Monsoon highway conditions monitored; express city door delivery running at full capacity.',
            'cutoff_days_prior' => 2,
            'greetings' => [
                'Haritalika Teej ko hardik mangalmaya subhakamana! 🌸 Best wishes to all our valued clients!',
            ],
        ],
    ],
];
