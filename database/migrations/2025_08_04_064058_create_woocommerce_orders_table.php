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
        Schema::create('woocommerce_orders', function (Blueprint $table) {
            $table->id('_id');
            $table->string('_uid')->unique();
            $table->unsignedBigInteger('woocommerce_integrations__id');
            $table->unsignedInteger('vendors__id');
            $table->unsignedInteger('contacts__id')->nullable();
            $table->unsignedInteger('woocommerce_order_id')->unique();
            $table->string('order_number');
            $table->string('status')->default('pending');
            $table->decimal('total', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('payment_method')->nullable();
            $table->string('payment_method_title')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('shipping_method_title')->nullable();
            $table->decimal('total_tax', 10, 2)->default(0);
            $table->decimal('total_shipping', 10, 2)->default(0);
            $table->decimal('total_discount', 10, 2)->default(0);
            $table->integer('total_items')->default(0);
            $table->text('customer_note')->nullable();
            $table->text('order_notes')->nullable();
            $table->timestamp('date_created')->nullable();
            $table->timestamp('date_modified')->nullable();
            $table->timestamp('date_completed')->nullable();
            $table->timestamp('date_paid')->nullable();
            $table->timestamp('date_processing')->nullable();
            $table->timestamp('date_on_hold')->nullable();
            $table->timestamp('date_cancelled')->nullable();
            $table->timestamp('date_refunded')->nullable();
            $table->json('customer_data')->nullable();
            $table->json('order_data')->nullable();
            $table->json('__data')->nullable();
            $table->timestamps();

            $table->foreign('woocommerce_integrations__id')->references('_id')->on('woocommerce_integrations')->onDelete('cascade');
            $table->foreign('vendors__id')->references('_id')->on('vendors')->onDelete('cascade');
            $table->foreign('contacts__id')->references('_id')->on('contacts')->onDelete('set null');
            $table->index(['vendors__id', 'status']);
            $table->index(['vendors__id', 'payment_method']);
            $table->index(['vendors__id', 'date_created']);
            $table->index(['woocommerce_order_id', 'vendors__id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woocommerce_orders');
    }
}; 