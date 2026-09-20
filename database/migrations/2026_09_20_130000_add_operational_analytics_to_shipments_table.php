<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Customer Acquisition Channel
            if (!Schema::hasColumn('shipments', 'acquisition_source')) {
                $table->string('acquisition_source')->default('direct_portal')->after('shipment_type');
            }

            // Destination Country explicit tracking column
            if (!Schema::hasColumn('shipments', 'destination_country')) {
                $table->string('destination_country')->nullable()->after('receiver_country');
            }

            // Financial & Commercial Metrics
            if (!Schema::hasColumn('shipments', 'price_quoted')) {
                $table->decimal('price_quoted', 12, 2)->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('shipments', 'price_sold')) {
                $table->decimal('price_sold', 12, 2)->nullable()->after('price_quoted');
            }
            if (!Schema::hasColumn('shipments', 'cost_amount')) {
                $table->decimal('cost_amount', 12, 2)->default(0)->after('price_sold');
            }
            if (!Schema::hasColumn('shipments', 'gross_margin')) {
                $table->decimal('gross_margin', 12, 2)->default(0)->after('cost_amount');
            }
            if (!Schema::hasColumn('shipments', 'gross_margin_percentage')) {
                $table->decimal('gross_margin_percentage', 5, 2)->default(0)->after('gross_margin');
            }

            // Vendor & Carrier Telemetry
            if (!Schema::hasColumn('shipments', 'vendor_name')) {
                $table->string('vendor_name')->nullable()->after('gross_margin_percentage');
            }
            if (!Schema::hasColumn('shipments', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('vendor_name');
            }

            // Operational Transit Time & SLA Tracking
            if (!Schema::hasColumn('shipments', 'transit_time_hours')) {
                $table->decimal('transit_time_hours', 8, 2)->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('shipments', 'transit_time_days')) {
                $table->decimal('transit_time_days', 5, 2)->nullable()->after('transit_time_hours');
            }
            if (!Schema::hasColumn('shipments', 'is_delayed')) {
                $table->boolean('is_delayed')->default(false)->after('transit_time_days');
            }
            if (!Schema::hasColumn('shipments', 'delay_hours')) {
                $table->decimal('delay_hours', 8, 2)->default(0)->after('is_delayed');
            }

            // Return to Origin (RTO) Tracking
            if (!Schema::hasColumn('shipments', 'is_returned')) {
                $table->boolean('is_returned')->default(false)->after('delay_hours');
            }
            if (!Schema::hasColumn('shipments', 'return_reason')) {
                $table->text('return_reason')->nullable()->after('is_returned');
            }
            if (!Schema::hasColumn('shipments', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('return_reason');
            }

            // Damage & Incident Claim Telemetry
            if (!Schema::hasColumn('shipments', 'is_damaged')) {
                $table->boolean('is_damaged')->default(false)->after('returned_at');
            }
            if (!Schema::hasColumn('shipments', 'damage_description')) {
                $table->text('damage_description')->nullable()->after('is_damaged');
            }
            if (!Schema::hasColumn('shipments', 'damage_reported_at')) {
                $table->timestamp('damage_reported_at')->nullable()->after('damage_description');
            }

            // Customer Complaints & Issue Counts
            if (!Schema::hasColumn('shipments', 'has_complaint')) {
                $table->boolean('has_complaint')->default(false)->after('damage_reported_at');
            }
            if (!Schema::hasColumn('shipments', 'complaint_count')) {
                $table->integer('complaint_count')->default(0)->after('has_complaint');
            }

            // Customer Retention & Repeat Purchase Telemetry
            if (!Schema::hasColumn('shipments', 'is_repeat_customer')) {
                $table->boolean('is_repeat_customer')->default(false)->after('complaint_count');
            }
            if (!Schema::hasColumn('shipments', 'customer_shipment_sequence')) {
                $table->integer('customer_shipment_sequence')->default(1)->after('is_repeat_customer');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $cols = [
                'acquisition_source', 'destination_country', 'price_quoted', 'price_sold',
                'cost_amount', 'gross_margin', 'gross_margin_percentage',
                'vendor_name', 'vendor_id', 'transit_time_hours', 'transit_time_days',
                'is_delayed', 'delay_hours', 'is_returned', 'return_reason', 'returned_at',
                'is_damaged', 'damage_description', 'damage_reported_at',
                'has_complaint', 'complaint_count', 'is_repeat_customer',
                'customer_shipment_sequence',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('shipments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
