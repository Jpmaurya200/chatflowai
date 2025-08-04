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
        Schema::create('shopify_orders', function (Blueprint $table) {
            $table->id('_id');
            $table->unsignedBigInteger('shopify_integrations__id');
            $table->unsignedInteger('vendors__id');
            $table->unsignedInteger('contacts__id')->nullable();
            $table->string('shopify_order_id')->unique();
            $table->string('order_number');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('financial_status')->default('pending');
            $table->string('fulfillment_status')->default('unfulfilled');
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('subtotal_price', 10, 2)->default(0);
            $table->decimal('total_tax', 10, 2)->default(0);
            $table->decimal('total_discounts', 10, 2)->default(0);
            $table->decimal('total_weight', 10, 2)->default(0);
            $table->integer('total_items')->default(0);
            $table->text('tags')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('open');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('created_at_shopify')->nullable();
            $table->timestamp('updated_at_shopify')->nullable();
            $table->timestamp('processed_at_shopify')->nullable();
            $table->timestamp('cancelled_at_shopify')->nullable();
            $table->timestamp('closed_at_shopify')->nullable();
            $table->json('__data')->nullable();
            $table->timestamps();

            $table->foreign('shopify_integrations__id')->references('_id')->on('shopify_integrations')->onDelete('cascade');
            $table->foreign('vendors__id')->references('_id')->on('vendors')->onDelete('cascade');
            $table->foreign('contacts__id')->references('_id')->on('contacts')->onDelete('set null');
            $table->index(['vendors__id', 'status']);
            $table->index(['vendors__id', 'financial_status']);
            $table->index(['vendors__id', 'fulfillment_status']);
            $table->index(['phone', 'vendors__id']);
            $table->index(['email', 'vendors__id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_orders');
    }
};
