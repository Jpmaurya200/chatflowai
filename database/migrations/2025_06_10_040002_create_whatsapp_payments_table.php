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
        Schema::create('whatsapp_payments', function (Blueprint $table) {
            $table->id('_id');
            $table->string('_uid')->unique();
            $table->string('payment_id', 100)->unique(); // Payment gateway ID
            $table->unsignedBigInteger('vendors__id');
            $table->string('order_id', 50);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
                'refunded',
                'partially_refunded'
            ])->default('pending');
            $table->string('payment_method', 50)->nullable(); // UPI, card, netbanking, etc.
            $table->string('transaction_id', 100)->nullable(); // Gateway transaction ID
            $table->string('payment_link_id', 100)->nullable();
            $table->string('payment_link_url', 500)->nullable();
            $table->string('gateway', 50)->default('razorpay'); // Payment gateway used
            $table->json('gateway_response')->nullable(); // Store gateway response
            $table->json('__data')->nullable(); // Additional payment data
            $table->timestamp('payment_initiated_at')->nullable();
            $table->timestamp('payment_completed_at')->nullable();
            $table->timestamp('payment_failed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['vendors__id', 'status']);
            $table->index(['order_id', 'vendors__id']);
            $table->index('payment_id');
            $table->index('transaction_id');
            $table->index('payment_link_id');
            $table->index('gateway');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_payments');
    }
};
