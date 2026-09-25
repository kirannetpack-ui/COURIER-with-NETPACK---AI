<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE shipments MODIFY COLUMN shipment_type VARCHAR(50) NOT NULL DEFAULT 'domestic'");
            DB::statement("ALTER TABLE shipments MODIFY COLUMN service_type VARCHAR(50) NOT NULL DEFAULT 'standard'");
        } else {
            Schema::table('shipments', function (Blueprint $table) {
                $table->string('shipment_type', 50)->default('domestic')->change();
                $table->string('service_type', 50)->default('standard')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE shipments MODIFY COLUMN shipment_type ENUM('grocery', 'document', 'parcel') NOT NULL DEFAULT 'grocery'");
            DB::statement("ALTER TABLE shipments MODIFY COLUMN service_type ENUM('economy', 'standard', 'express') NOT NULL DEFAULT 'standard'");
        }
    }
};
