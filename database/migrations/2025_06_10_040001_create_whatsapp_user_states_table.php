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
        Schema::create('whatsapp_user_states', function (Blueprint $table) {
            $table->id('_id');
            $table->string('_uid')->unique();
            $table->unsignedBigInteger('vendors__id');
            $table->unsignedBigInteger('contacts__id')->nullable();
            $table->string('phone', 20);
            $table->string('state', 50); // awaiting_address, awaiting_payment, etc.
            $table->string('order_id', 50)->nullable();
            $table->string('context', 100)->nullable(); // Additional context for the state
            $table->json('__data')->nullable(); // Store state-specific data
            $table->timestamp('expires_at')->nullable(); // State expiration
            $table->timestamps();
            
            // Indexes
            $table->index(['vendors__id', 'phone']);
            $table->index(['vendors__id', 'state']);
            $table->index('order_id');
            $table->index('expires_at');
            
            // Unique constraint to ensure one active state per user per vendor
            $table->unique(['vendors__id', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_user_states');
    }
};
