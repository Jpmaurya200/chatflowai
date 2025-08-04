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
        Schema::create('shopify_order_notifications', function (Blueprint $table) {
            $table->id('_id');
            $table->unsignedBigInteger('shopify_orders__id');
            $table->unsignedInteger('vendors__id');
            $table->unsignedInteger('contacts__id')->nullable();
            $table->string('notification_type');
            $table->string('status')->default('pending');
            $table->string('message_id')->nullable();
            $table->string('whatsapp_message_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('__data')->nullable();
            $table->timestamps();

            $table->foreign('shopify_orders__id')->references('_id')->on('shopify_orders')->onDelete('cascade');
            $table->foreign('vendors__id')->references('_id')->on('vendors')->onDelete('cascade');
            $table->foreign('contacts__id')->references('_id')->on('contacts')->onDelete('set null');
            $table->index(['vendors__id', 'status'], 'shopify_notifications_vendor_status');
            $table->index(['vendors__id', 'notification_type'], 'shopify_notifications_vendor_type');
            $table->index(['shopify_orders__id', 'notification_type'], 'shopify_notifications_order_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_order_notifications');
    }
};
