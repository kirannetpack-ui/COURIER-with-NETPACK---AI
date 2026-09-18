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
        Schema::table('pickup_requests', function (Blueprint $table) {
            if (Schema::hasColumn('pickup_requests', 'delivery_address')) {
                $table->string('delivery_address')->nullable()->change();
            }
            if (Schema::hasColumn('pickup_requests', 'delivery_ward_no')) {
                $table->string('delivery_ward_no')->nullable()->change();
            }
            if (Schema::hasColumn('pickup_requests', 'delivery_municipality')) {
                $table->string('delivery_municipality')->nullable()->change();
            }
            if (Schema::hasColumn('pickup_requests', 'delivery_district')) {
                $table->string('delivery_district')->nullable()->change();
            }
            if (Schema::hasColumn('pickup_requests', 'delivery_province')) {
                $table->string('delivery_province')->nullable()->change();
            }
            if (Schema::hasColumn('pickup_requests', 'delivery_city')) {
                $table->string('delivery_city')->nullable()->change();
            }
            if (Schema::hasColumn('pickup_requests', 'customer_name')) {
                $table->string('customer_name')->nullable()->change();
            }
            if (Schema::hasColumn('pickup_requests', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep permissive nullability to prevent data loss or breakage
    }
};
