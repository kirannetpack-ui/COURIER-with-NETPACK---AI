<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\RiderProfile;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class EcommerceTestSeeder extends Seeder
{
    public function run()
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('E-commerce test data may only be seeded in local or testing environments.');
        }

        $this->command?->info('🛒 Seeding E-commerce test data for Seller & Rider...');

        // 1. Create or update sellers (both seller@netpack.test and seller@test.com)
        $sellerAccounts = [
            ['name' => 'E-commerce Seller', 'email' => 'seller@netpack.test'],
            ['name' => 'Test Seller', 'email' => 'seller@test.com'],
        ];

        $sellers = [];
        foreach ($sellerAccounts as $s) {
            $seller = User::updateOrCreate(
                ['email' => $s['email']],
                [
                    'name' => $s['name'],
                    'password' => Hash::make('Netpack!Seller#2026'),
                    'user_type' => 'seller',
                    'verification_status' => 'approved',
                    'phone' => '9800000000',
                    'registration_completed' => true,
                    'password_changed' => true,
                    'is_online' => true,
                    'is_available' => true,
                ]
            );

            Wallet::firstOrCreate(
                ['user_id' => $seller->id],
                [
                    'user_type' => 'seller',
                    'balance' => 15000.00,
                ]
            );

            $sellers[] = $seller;
            $this->command?->info('✅ Seller ready: ' . $seller->email);
        }

        // 2. Create or update riders (both rider@netpack.test and rider@test.com)
        $riderAccounts = [
            ['name' => 'Delivery Rider', 'email' => 'rider@netpack.test'],
            ['name' => 'Test Rider', 'email' => 'rider@test.com'],
        ];

        $riders = [];
        foreach ($riderAccounts as $r) {
            $rider = User::updateOrCreate(
                ['email' => $r['email']],
                [
                    'name' => $r['name'],
                    'password' => Hash::make('Netpack!Rider#2026'),
                    'user_type' => 'rider',
                    'verification_status' => 'approved',
                    'phone' => '9800000001',
                    'is_online' => true,
                    'is_available' => true,
                    'vehicle_type' => 'motorcycle',
                    'registration_completed' => true,
                    'password_changed' => true,
                ]
            );

            Wallet::firstOrCreate(
                ['user_id' => $rider->id],
                [
                    'user_type' => 'rider',
                    'balance' => 5000.00,
                ]
            );

            RiderProfile::updateOrCreate(
                ['user_id' => $rider->id],
                [
                    'rider_code' => 'RDR-' . str_pad($rider->id, 4, '0', STR_PAD_LEFT),
                    'full_name' => $rider->name,
                    'email' => $rider->email,
                    'mobile' => '9800000001',
                    'vehicle_type' => 'motorcycle',
                    'vehicle_number' => 'BA-99-PA-1234',
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                    'availability_status' => 'online',
                    'cod_level' => 'level_3',
                    'cod_limit' => 50000.00,
                    'current_outstanding_cod' => 0.00,
                    'trust_score' => 100,
                    'rating' => 5.0,
                ]
            );

            $riders[] = $rider;
            $this->command?->info('✅ Rider ready: ' . $rider->email);
        }

        // 3. Create products for each seller
        $productTemplates = [
            ['name' => 'Nepali Rice (10kg)', 'price' => 1200, 'category' => 'Groceries', 'stock' => 50],
            ['name' => 'Cooking Oil (5L)', 'price' => 800, 'category' => 'Groceries', 'stock' => 30],
            ['name' => 'Salt (1kg)', 'price' => 150, 'category' => 'Groceries', 'stock' => 100],
            ['name' => 'Sugar (1kg)', 'price' => 200, 'category' => 'Groceries', 'stock' => 80],
            ['name' => 'Tea (100g)', 'price' => 300, 'category' => 'Beverages', 'stock' => 40],
            ['name' => 'Coffee (200g)', 'price' => 500, 'category' => 'Beverages', 'stock' => 35],
            ['name' => 'Noodles (1 pack)', 'price' => 80, 'category' => 'Snacks', 'stock' => 200],
            ['name' => 'Biscuits (200g)', 'price' => 120, 'category' => 'Snacks', 'stock' => 150],
            ['name' => 'Cooking Gas (1 cylinder)', 'price' => 1500, 'category' => 'Utilities', 'stock' => 20],
            ['name' => 'Drinking Water (20L)', 'price' => 80, 'category' => 'Beverages', 'stock' => 60],
        ];

        $allCreatedProducts = [];

        foreach ($sellers as $seller) {
            foreach ($productTemplates as $data) {
                $sku = 'SKU-' . $seller->id . '-' . strtoupper(substr(str_replace(' ', '', $data['name']), 0, 4));
                $product = Product::firstOrCreate(
                    [
                        'user_id' => $seller->id,
                        'sku' => $sku,
                    ],
                    [
                        'name' => $data['name'],
                        'price_npr' => $data['price'],
                        'category' => $data['category'],
                        'stock_quantity' => $data['stock'],
                        'weight_kg' => 1.0,
                        'origin_country' => 'Nepal',
                        'origin_city' => 'Kathmandu',
                        'is_active' => true,
                        'is_featured' => false,
                        'description' => 'High quality ' . $data['name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $allCreatedProducts[$seller->id][] = $product;
            }
        }

        // 4. Create test orders and deliveries for both sellers and riders
        $statuses = ['pending', 'assigned', 'picked_up', 'out_for_delivery', 'delivered', 'cancelled'];
        $customers = [
            ['name' => 'Ram Sharma', 'phone' => '9800000101'],
            ['name' => 'Sita Gurung', 'phone' => '9800000102'],
            ['name' => 'Hari Rana', 'phone' => '9800000103'],
            ['name' => 'Gita Thapa', 'phone' => '9800000104'],
            ['name' => 'Bikash Pandey', 'phone' => '9800000105'],
        ];

        $ordersCreated = 0;

        foreach ($sellers as $sellerIdx => $seller) {
            $sellerProducts = $allCreatedProducts[$seller->id] ?? [];
            if (empty($sellerProducts)) continue;

            $assignedRider = $riders[$sellerIdx % count($riders)];

            foreach ($statuses as $index => $status) {
                $customer = $customers[$index % count($customers)];
                $orderNumber = 'ORD-' . $seller->id . '-' . strtoupper(substr($status, 0, 3)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

                $existingOrder = Order::where('order_number', $orderNumber)->first();
                if ($existingOrder) {
                    continue;
                }

                $orderItemsCount = rand(1, 2);
                $subtotal = 0;
                $items = [];

                for ($i = 0; $i < $orderItemsCount; $i++) {
                    $prod = $sellerProducts[array_rand($sellerProducts)];
                    $quantity = rand(1, 2);
                    $price = $prod->price_npr;
                    $subtotal += $price * $quantity;
                    $items[] = [
                        'product' => $prod,
                        'quantity' => $quantity,
                        'price' => $price,
                    ];
                }

                $tax = round($subtotal * 0.13, 2);
                $shippingCost = rand(80, 150);
                $total = round($subtotal + $tax + $shippingCost, 2);

                $isRiderAssigned = in_array($status, ['assigned', 'picked_up', 'out_for_delivery', 'delivered']);
                $orderRiderId = $isRiderAssigned ? $assignedRider->id : null;

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'seller_id' => $seller->id,
                    'rider_id' => $orderRiderId,
                    'customer_name' => $customer['name'],
                    'customer_phone' => $customer['phone'],
                    'shipping_address' => 'Kathmandu Hub Sector ' . ($index + 1) . ', Nepal',
                    'total_amount' => $total,
                    'tax_amount' => $tax,
                    'shipping_cost' => $shippingCost,
                    'discount_amount' => 0,
                    'grand_total' => $total,
                    'status' => $status,
                    'payment_status' => $status === 'delivered' ? 'paid' : 'pending',
                    'tracking_number' => 'TRK' . strtoupper(uniqid()),
                    'rider_assigned_at' => $isRiderAssigned ? now()->subHours(rand(1, 6)) : null,
                    'rider_acceptance_time' => $isRiderAssigned ? now()->subHours(rand(1, 6)) : null,
                    'picked_up_at' => in_array($status, ['picked_up', 'out_for_delivery', 'delivered']) ? now()->subHours(rand(1, 4)) : null,
                    'out_for_delivery_at' => in_array($status, ['out_for_delivery', 'delivered']) ? now()->subHours(rand(1, 2)) : null,
                    'delivered_at' => $status === 'delivered' ? now()->subHours(1) : null,
                    'created_at' => now()->subHours(rand(2, 24)),
                    'updated_at' => now(),
                ]);

                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['price'] * $item['quantity'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // If assigned to rider, also create Delivery record
                if ($isRiderAssigned && class_exists(Delivery::class)) {
                    $deliveryStatus = match ($status) {
                        'out_for_delivery' => 'in_transit',
                        'assigned' => 'assigned',
                        'picked_up' => 'picked_up',
                        'delivered' => 'delivered',
                        'cancelled' => 'cancelled',
                        default => 'assigned',
                    };

                    Delivery::create([
                        'rider_id' => $assignedRider->id,
                        'delivery_type' => 'ecommerce',
                        'pickup_address' => 'Kathmandu Central Warehouse, Ward 4',
                        'delivery_address' => $order->shipping_address,
                        'customer_name' => $order->customer_name,
                        'customer_phone' => $order->customer_phone,
                        'status' => $deliveryStatus,
                        'assigned_at' => $order->rider_assigned_at,
                        'accepted_at' => $order->rider_acceptance_time,
                        'picked_up_at' => $order->picked_up_at,
                        'in_transit_at' => $order->out_for_delivery_at,
                        'delivered_at' => $order->delivered_at,
                        'delivery_fee' => 120.00,
                        'rider_earnings' => 100.00,
                        'cod_amount' => $total,
                        'is_cod' => true,
                    ]);
                }

                $ordersCreated++;
            }
        }

        $this->command?->info('🎉 E-commerce test data seeded successfully!');
        $this->command?->info('   - Sellers: ' . count($sellers));
        $this->command?->info('   - Riders: ' . count($riders));
        $this->command?->info('   - Orders created: ' . $ordersCreated);
    }
}
