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
        if (!Schema::hasTable('saved_addresses')) {
            Schema::create('saved_addresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('label')->nullable(); // e.g. "Head Office", "Warehouse A", "Home"
                $table->string('type')->default('pickup'); // 'pickup', 'delivery', 'general'
                $table->string('contact_person_name');
                $table->string('contact_person_phone');
                $table->text('address');
                $table->string('landmark')->nullable();
                $table->string('city')->default('Kathmandu');
                $table->string('area')->nullable();
                $table->boolean('is_default')->default(false);
                $table->unsignedInteger('usage_count')->default(1);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'is_default']);
                $table->index(['user_id', 'last_used_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_addresses');
    }
};
