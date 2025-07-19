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
        Schema::table('whatsapp_orders', function (Blueprint $table) {
            // Drop the existing enum constraint and recreate with new values
            $table->dropColumn('status');
        });

        Schema::table('whatsapp_orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'awaiting_address', 
                'awaiting_payment',
                'payment_processing',
                'paid',
                'confirmed',
                'shipped',
                'delivered',
                'cancelled',
                'refunded',
                'manual_payment_required'
            ])->default('pending')->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('whatsapp_orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'awaiting_address', 
                'awaiting_payment',
                'payment_processing',
                'paid',
                'confirmed',
                'shipped',
                'delivered',
                'cancelled',
                'refunded'
            ])->default('pending')->after('currency');
        });
    }
};
