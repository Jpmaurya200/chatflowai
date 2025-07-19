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
        Schema::create('whatsapp_orders', function (Blueprint $table) {
            $table->id('_id');
            $table->string('_uid')->unique();
            $table->string('order_id', 50)->unique(); // Custom order ID
            $table->unsignedBigInteger('vendors__id');
            $table->unsignedBigInteger('contacts__id')->nullable();
            $table->string('customer_phone', 20);
            $table->string('customer_name')->nullable();
            $table->json('items'); // Order items from catalog
            $table->decimal('total_amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->text('delivery_address')->nullable();
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
            ])->default('pending');
            $table->string('payment_id', 100)->nullable();
            $table->string('payment_status', 50)->nullable();
            $table->string('currency', 3)->default('INR');
            $table->json('__data')->nullable(); // Additional order data
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('payment_completed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['vendors__id', 'status']);
            $table->index(['customer_phone', 'vendors__id']);
            $table->index(['order_id', 'vendors__id']);
            $table->index('payment_id');
            $table->index('ordered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_orders');
    }
};
