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
        Schema::create('hs_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->index(); // e.g. 6214.20.00
            $table->string('wco_code', 10)->index(); // 6-digit WCO HS code e.g. 6214.20
            $table->string('nepal_tariff_code', 20)->nullable()->index(); // 8-digit Nepal Customs Tariff
            $table->string('commodity_name'); // English commodity description
            $table->text('description')->nullable(); // Extended details / scope
            $table->string('category', 100)->index(); // Textiles, Agro, Handicrafts, etc.
            $table->string('standard_uom', 20)->default('PCS'); // PCS, KGS, SETS, MTR, PRS, BOX, DOZ
            $table->decimal('export_duty_rate', 8, 2)->default(0); // Standard export duty reference
            $table->string('customs_notes')->nullable(); // Compliance notes (e.g. Origin cert required)
            $table->boolean('is_popular')->default(false)->index(); // Top Nepal export commodities
            $table->text('search_keywords')->nullable(); // Synonyms for search
            $table->timestamps();

            $table->index(['commodity_name', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hs_codes');
    }
};
