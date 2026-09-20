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
            if (!Schema::hasColumn('shipments', 'invoice_data')) {
                $table->json('invoice_data')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'packing_list_data')) {
                $table->json('packing_list_data')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'invoice_file')) {
                $table->string('invoice_file')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'seller_bill_file')) {
                $table->string('seller_bill_file')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('shipments', 'invoice_data')) {
                $columnsToDrop[] = 'invoice_data';
            }
            if (Schema::hasColumn('shipments', 'packing_list_data')) {
                $columnsToDrop[] = 'packing_list_data';
            }
            if (Schema::hasColumn('shipments', 'invoice_file')) {
                $columnsToDrop[] = 'invoice_file';
            }
            if (Schema::hasColumn('shipments', 'seller_bill_file')) {
                $columnsToDrop[] = 'seller_bill_file';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
