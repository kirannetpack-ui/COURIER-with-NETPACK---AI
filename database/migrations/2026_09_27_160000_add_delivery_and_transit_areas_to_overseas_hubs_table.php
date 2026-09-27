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
        if (Schema::hasTable('overseas_hubs')) {
            Schema::table('overseas_hubs', function (Blueprint $table) {
                if (!Schema::hasColumn('overseas_hubs', 'main_delivery_countries')) {
                    $table->json('main_delivery_countries')->nullable()->after('coverage_countries');
                }
                if (!Schema::hasColumn('overseas_hubs', 'transit_countries')) {
                    $table->json('transit_countries')->nullable()->after('main_delivery_countries');
                }
            });
        }

        // Pivot table allowing many-to-many relationship between hubs and user partners / overseas partners
        if (!Schema::hasTable('hub_partners')) {
            Schema::create('hub_partners', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hub_id')->constrained('overseas_hubs')->onDelete('cascade');
                $table->unsignedBigInteger('partner_id'); // Can reference users or overseas_partners
                $table->string('partner_type')->default('agency'); // 'agency', 'user_partner', 'overseas_partner'
                $table->string('service_role')->default('handling_and_clearance'); // 'handling_and_clearance', 'last_mile', 'transit_linehaul'
                $table->json('covered_countries')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['hub_id', 'partner_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('overseas_hubs')) {
            Schema::table('overseas_hubs', function (Blueprint $table) {
                if (Schema::hasColumn('overseas_hubs', 'main_delivery_countries')) {
                    $table->dropColumn('main_delivery_countries');
                }
                if (Schema::hasColumn('overseas_hubs', 'transit_countries')) {
                    $table->dropColumn('transit_countries');
                }
            });
        }

        Schema::dropIfExists('hub_partners');
    }
};
