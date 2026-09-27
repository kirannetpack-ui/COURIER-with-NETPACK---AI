<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OverseasHub;
use App\Models\Agency;
use App\Models\AgencyStaff;
use App\Models\LastMileCarrier;
use App\Models\MAWB;
use Illuminate\Support\Facades\Hash;

class InternationalHubsAndAgenciesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Default Overseas Hubs
        $hubsData = [
            [
                'hub_code' => 'DXB',
                'hub_name' => 'Dubai Global Air Hub',
                'location' => 'Dubai, United Arab Emirates',
                'country' => 'United Arab Emirates',
                'hub_type' => 'main_hub',
                'coverage_countries' => ['United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Oman', 'Kuwait', 'Bahrain', 'Canada', 'United States'],
                'service_routes' => 'Includes all Gulf Countries, Crossing by UPS service to worldwide & Canada DDP service whose route is directly to Toronto and delivery is done by Canpar or Obibox or local courier company.',
                'mode_type' => 'DDP & Cross Worldwide',
                'address' => 'Dubai Cargo Village, Cargo Mega Terminal, DXB Airport, Dubai, UAE',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'hub_code' => 'LHR',
                'hub_name' => 'United Kingdom & Europe Gateway Hub',
                'location' => 'London Heathrow, United Kingdom',
                'country' => 'United Kingdom',
                'hub_type' => 'main_hub',
                'coverage_countries' => ['United Kingdom', 'Germany', 'France', 'Netherlands', 'Italy', 'Spain', 'Belgium', 'United States', 'Canada'],
                'service_routes' => 'Handles UK, EUROPE under DDP mode, USA & CANADA under DDU mode.',
                'mode_type' => 'UK/EU DDP & USA/CA DDU',
                'address' => 'World Cargo Centre, Heathrow Airport, London TW6 3NW, United Kingdom',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'hub_code' => 'SYD',
                'hub_name' => 'Australia Pacific Central Hub',
                'location' => 'Sydney Mascot, Australia',
                'country' => 'Australia',
                'hub_type' => 'main_hub',
                'coverage_countries' => ['Australia'],
                'service_routes' => 'Handling whole of Australia nationwide air and road linehaul distribution.',
                'mode_type' => 'Australia Nationwide',
                'address' => 'Sydney International Airport Cargo Precinct, Link Rd, Mascot NSW 2020, Australia',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'hub_code' => 'AKL',
                'hub_name' => 'New Zealand Air Freight Hub',
                'location' => 'Auckland Mangere, New Zealand',
                'country' => 'New Zealand',
                'hub_type' => 'main_hub',
                'coverage_countries' => ['New Zealand'],
                'service_routes' => 'Handling all New Zealand North and South Island loads.',
                'mode_type' => 'NZ Nationwide',
                'address' => 'Auckland Airport Air Cargo Complex, George Bolt Memorial Dr, Mangere, Auckland 2022, New Zealand',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        $createdHubs = [];
        foreach ($hubsData as $data) {
            $auto = OverseasHub::autoFigureOutCoverage($data['country'], $data['hub_code']);
            $data['main_delivery_countries'] = $auto['main_delivery_countries'];
            $data['transit_countries'] = $auto['transit_countries'];
            $hub = OverseasHub::updateOrCreate(
                ['hub_code' => $data['hub_code']],
                $data
            );
            $createdHubs[$data['hub_code']] = $hub;
        }

        // 2. Seed Default Agencies under Hubs
        $agenciesData = [
            [
                'hub_id' => $createdHubs['DXB']->id,
                'code' => 'DXB-AGY01',
                'name' => 'NETPACK Gulf & Transit Agency LLC',
                'country' => 'United Arab Emirates',
                'city' => 'Dubai',
                'address' => 'Suite 402, Freight Gate 5, Dubai Cargo Village, Dubai',
                'phone' => '+971-4-2821234',
                'phone_secondary' => '+971-50-9876543',
                'primary_contact' => 'Tariq Al-Mansoor (Operations Director)',
                'email' => 'ops.dubai@netpackcargo.com',
                'notification_emails' => [
                    'ops.dubai@netpackcargo.com',
                    'clearance.dxb@netpackcargo.com',
                    'manifest.dxb@agencyhub.ae',
                ],
                'operational_notes' => 'Direct Dubai customs broker, UPS world cross injection point, and Toronto DDP container consolidator.',
                'password' => Hash::make('Dubai@Netpack2026'),
                'is_active' => true,
            ],
            [
                'hub_id' => $createdHubs['LHR']->id,
                'code' => 'LHR-AGY01',
                'name' => 'NETPACK UK & European Logistics Ltd',
                'country' => 'United Kingdom',
                'city' => 'London',
                'address' => 'Unit 8, Skyport Cargo Estate, Heathrow, Hounslow TW4 6JQ',
                'phone' => '+44-20-8897-5500',
                'phone_secondary' => '+44-7700-900123',
                'primary_contact' => 'James Richardson (Head of Customs)',
                'email' => 'clearance.uk@netpackcargo.com',
                'notification_emails' => [
                    'clearance.uk@netpackcargo.com',
                    'import.lhr@netpackcargo.com',
                    'eu.ddp@agencyhub.co.uk',
                ],
                'operational_notes' => 'HMRC badge cleared. DDP processing for UK and EU destinations; DDU linehaul transfers to North America.',
                'password' => Hash::make('London@Netpack2026'),
                'is_active' => true,
            ],
            [
                'hub_id' => $createdHubs['SYD']->id,
                'code' => 'SYD-AGY01',
                'name' => 'Pacific Courier Agency Pty Ltd',
                'country' => 'Australia',
                'city' => 'Sydney',
                'address' => '12 Freightways Circuit, Sydney Airport, Mascot NSW 2020',
                'phone' => '+61-2-9352-7700',
                'phone_secondary' => '+61-400-112233',
                'primary_contact' => 'Liam O\'Connor (Managing Director)',
                'email' => 'sydney.ops@netpackcargo.com',
                'notification_emails' => [
                    'sydney.ops@netpackcargo.com',
                    'aus.customs@pacificagency.com.au',
                ],
                'operational_notes' => 'Australian Border Force accredited. Direct depot connection to Australia Post and StarTrack.',
                'password' => Hash::make('Sydney@Netpack2026'),
                'is_active' => true,
            ],
            [
                'hub_id' => $createdHubs['AKL']->id,
                'code' => 'AKL-AGY01',
                'name' => 'Aotearoa Express Cargo Agency',
                'country' => 'New Zealand',
                'city' => 'Auckland',
                'address' => 'Cargo Terminal 3, Auckland International Airport, Auckland 2022',
                'phone' => '+64-9-256-4400',
                'phone_secondary' => '+64-21-987654',
                'primary_contact' => 'Sarah Te Wiata (Station Manager)',
                'email' => 'nz.clearance@netpackcargo.com',
                'notification_emails' => [
                    'nz.clearance@netpackcargo.com',
                    'auckland.hub@nzcargo.co.nz',
                ],
                'operational_notes' => 'MPI biosecurity cleared. Full coverage of North & South Islands via NZ Post.',
                'password' => Hash::make('Auckland@Netpack2026'),
                'is_active' => true,
            ],
        ];

        $createdAgencies = [];
        foreach ($agenciesData as $aData) {
            $agency = Agency::updateOrCreate(
                ['code' => $aData['code']],
                $aData
            );
            $createdAgencies[$aData['code']] = $agency;

            // Seed Agency Staff member
            AgencyStaff::updateOrCreate(
                ['email' => 'staff.' . strtolower(substr($aData['code'], 0, 3)) . '@netpack.com'],
                [
                    'agency_id' => $agency->id,
                    'name' => explode(' ', $aData['primary_contact'])[0] . ' Staff',
                    'email' => 'staff.' . strtolower(substr($aData['code'], 0, 3)) . '@netpack.com',
                    'password' => Hash::make('Staff@12345'),
                    'phone' => $aData['phone'],
                    'position' => 'Cargo Terminal Operations Officer',
                    'role' => 'scanner',
                    'can_scan_arrival' => true,
                    'can_scan_departure' => true,
                    'can_add_notes' => true,
                    'is_active' => true,
                ]
            );
        }

        // 3. Seed Last Mile Delivery Companies
        $carriersData = [
            // Canada (routed through Dubai / direct)
            [
                'name' => 'Canpar Express',
                'code' => 'CANPAR',
                'hub_id' => $createdHubs['DXB']->id,
                'country' => 'Canada',
                'service_mode' => 'DDP Courier',
                'tracking_url_template' => 'https://www.canpar.com/en/track/tracking.jsp?reference={tracking}',
                'contact_email' => 'support@canpar.com',
                'contact_phone' => '+1-800-387-9335',
                'sort_order' => 1,
            ],
            [
                'name' => 'Obibox Courier',
                'code' => 'OBIBOX',
                'hub_id' => $createdHubs['DXB']->id,
                'country' => 'Canada',
                'service_mode' => 'DDP Final Mile',
                'tracking_url_template' => 'https://track.obibox.com/?tracking={tracking}',
                'contact_email' => 'help@obibox.com',
                'contact_phone' => '+1-888-624-2699',
                'sort_order' => 2,
            ],
            // Gulf / Worldwide Crossing
            [
                'name' => 'UPS Worldwide (Dubai Hub Crossing)',
                'code' => 'UPS-DXB',
                'hub_id' => $createdHubs['DXB']->id,
                'country' => 'Global Crossing',
                'service_mode' => 'Express Crossing',
                'tracking_url_template' => 'https://www.ups.com/track?tracknum={tracking}',
                'contact_email' => 'customer.gulf@ups.com',
                'contact_phone' => '+971-4-8078000',
                'sort_order' => 3,
            ],
            [
                'name' => 'Aramex Middle East',
                'code' => 'ARAMEX-ME',
                'hub_id' => $createdHubs['DXB']->id,
                'country' => 'United Arab Emirates',
                'service_mode' => 'GCC Courier',
                'tracking_url_template' => 'https://www.aramex.com/track/results?mode=0&ShipmentNumber={tracking}',
                'contact_email' => 'support@aramex.com',
                'contact_phone' => '+971-600-544000',
                'sort_order' => 4,
            ],
            // UK & Europe
            [
                'name' => 'Royal Mail',
                'code' => 'ROYALMAIL',
                'hub_id' => $createdHubs['LHR']->id,
                'country' => 'United Kingdom',
                'service_mode' => 'DDP Tracked',
                'tracking_url_template' => 'https://www.royalmail.com/track-your-item#/tracking-results/{tracking}',
                'contact_email' => 'business@royalmail.com',
                'contact_phone' => '+44-3457-740740',
                'sort_order' => 5,
            ],
            [
                'name' => 'DPD UK & Europe',
                'code' => 'DPD-EU',
                'hub_id' => $createdHubs['LHR']->id,
                'country' => 'European Union',
                'service_mode' => 'DDP Euro Express',
                'tracking_url_template' => 'https://track.dpd.co.uk/search?parcel={tracking}',
                'contact_email' => 'support@dpd.co.uk',
                'contact_phone' => '+44-121-275-0500',
                'sort_order' => 6,
            ],
            [
                'name' => 'FedEx Express (USA & Canada DDU)',
                'code' => 'FEDEX-LHR',
                'hub_id' => $createdHubs['LHR']->id,
                'country' => 'United States',
                'service_mode' => 'DDU Air Cargo',
                'tracking_url_template' => 'https://www.fedex.com/fedextrack/?trknbr={tracking}',
                'contact_email' => 'express@fedex.com',
                'contact_phone' => '+1-800-463-3339',
                'sort_order' => 7,
            ],
            // Australia
            [
                'name' => 'Australia Post',
                'code' => 'AUSPOST',
                'hub_id' => $createdHubs['SYD']->id,
                'country' => 'Australia',
                'service_mode' => 'Domestic Parcel Post',
                'tracking_url_template' => 'https://auspost.com.au/mypost/track/#/details/{tracking}',
                'contact_email' => 'support@auspost.com.au',
                'contact_phone' => '+61-13-76-78',
                'sort_order' => 8,
            ],
            [
                'name' => 'StarTrack Australia',
                'code' => 'STARTRACK',
                'hub_id' => $createdHubs['SYD']->id,
                'country' => 'Australia',
                'service_mode' => 'Express Road Linehaul',
                'tracking_url_template' => 'https://startrack.com.au/track/search?id={tracking}',
                'contact_email' => 'service@startrack.com.au',
                'contact_phone' => '+61-13-23-45',
                'sort_order' => 9,
            ],
            // New Zealand
            [
                'name' => 'NZ Post / CourierPost',
                'code' => 'NZPOST',
                'hub_id' => $createdHubs['AKL']->id,
                'country' => 'New Zealand',
                'service_mode' => 'Courier Tracked',
                'tracking_url_template' => 'https://www.nzpost.co.nz/tools/tracking?track={tracking}',
                'contact_email' => 'enquiries@nzpost.co.nz',
                'contact_phone' => '+64-800-501-501',
                'sort_order' => 10,
            ],
        ];

        foreach ($carriersData as $cData) {
            LastMileCarrier::updateOrCreate(
                ['code' => $cData['code']],
                $cData
            );
        }

        // 4. Pre-Feed Initial MAWB (Master Air Waybill) Inventory Pool
        $mawbsData = [
            // Emirates Airline to Dubai Hub
            [
                'mawb_number' => '176-54819203',
                'airline_name' => 'Emirates SkyCargo',
                'airline_code' => 'EK',
                'origin_airport' => 'KTM - Tribhuvan International',
                'destination_airport' => 'DXB - Dubai International',
                'hub_id' => $createdHubs['DXB']->id,
                'flight_number' => 'EK2355',
                'flight_date' => now()->addDays(1)->format('Y-m-d'),
                'status' => 'unused',
                'notes' => 'Scheduled daily A380/B777 belly cargo KTM-DXB. Unused inventory available for manifest assignment.',
            ],
            [
                'mawb_number' => '176-54819214',
                'airline_name' => 'Emirates SkyCargo',
                'airline_code' => 'EK',
                'origin_airport' => 'KTM - Tribhuvan International',
                'destination_airport' => 'DXB - Dubai International',
                'hub_id' => $createdHubs['DXB']->id,
                'flight_number' => 'EK2357',
                'flight_date' => now()->addDays(2)->format('Y-m-d'),
                'status' => 'unused',
                'notes' => 'Emirates cargo allocation for Gulf & Toronto consolidations.',
            ],
            [
                'mawb_number' => '176-54819225',
                'airline_name' => 'Emirates SkyCargo',
                'airline_code' => 'EK',
                'origin_airport' => 'KTM - Tribhuvan International',
                'destination_airport' => 'DXB - Dubai International',
                'hub_id' => $createdHubs['DXB']->id,
                'flight_number' => 'EK2355',
                'flight_date' => now()->addDays(3)->format('Y-m-d'),
                'status' => 'unused',
                'notes' => 'Backup Emirates inventory.',
            ],
            // Qatar Airways to London / Europe Hub
            [
                'mawb_number' => '157-78192041',
                'airline_name' => 'Qatar Airways Cargo',
                'airline_code' => 'QR',
                'origin_airport' => 'KTM - Tribhuvan International',
                'destination_airport' => 'LHR - London Heathrow',
                'hub_id' => $createdHubs['LHR']->id,
                'flight_number' => 'QR651',
                'flight_date' => now()->addDays(1)->format('Y-m-d'),
                'status' => 'unused',
                'notes' => 'KTM-DOH-LHR priority air cargo block space for UK & European DDP loads.',
            ],
            [
                'mawb_number' => '157-78192052',
                'airline_name' => 'Qatar Airways Cargo',
                'airline_code' => 'QR',
                'origin_airport' => 'KTM - Tribhuvan International',
                'destination_airport' => 'LHR - London Heathrow',
                'hub_id' => $createdHubs['LHR']->id,
                'flight_number' => 'QR653',
                'flight_date' => now()->addDays(2)->format('Y-m-d'),
                'status' => 'unused',
                'notes' => 'Qatar Airways European corridor air waybill.',
            ],
            // Singapore Airlines / Air India to Australia Hub
            [
                'mawb_number' => '618-90182743',
                'airline_name' => 'Singapore Airlines Cargo',
                'airline_code' => 'SQ',
                'origin_airport' => 'KTM - Tribhuvan International',
                'destination_airport' => 'SYD - Sydney International',
                'hub_id' => $createdHubs['SYD']->id,
                'flight_number' => 'SQ441',
                'flight_date' => now()->addDays(2)->format('Y-m-d'),
                'status' => 'unused',
                'notes' => 'KTM-SIN-SYD Australia continent air cargo allocation.',
            ],
            // Air New Zealand / Transit to Auckland
            [
                'mawb_number' => '086-44321980',
                'airline_name' => 'Air New Zealand Cargo',
                'airline_code' => 'NZ',
                'origin_airport' => 'KTM - Tribhuvan International',
                'destination_airport' => 'AKL - Auckland International',
                'hub_id' => $createdHubs['AKL']->id,
                'flight_number' => 'NZ284',
                'flight_date' => now()->addDays(3)->format('Y-m-d'),
                'status' => 'unused',
                'notes' => 'New Zealand loads air waybill inventory.',
            ],
            // flydubai
            [
                'mawb_number' => '141-39201945',
                'airline_name' => 'flydubai Cargo',
                'airline_code' => 'FZ',
                'origin_airport' => 'KTM - Tribhuvan International',
                'destination_airport' => 'DXB - Dubai International',
                'hub_id' => $createdHubs['DXB']->id,
                'flight_number' => 'FZ576',
                'flight_date' => now()->addDays(1)->format('Y-m-d'),
                'status' => 'unused',
                'notes' => 'flydubai daily direct service KTM-DXB.',
            ],
        ];

        foreach ($mawbsData as $mData) {
            MAWB::updateOrCreate(
                ['mawb_number' => $mData['mawb_number']],
                $mData
            );
        }
    }
}
