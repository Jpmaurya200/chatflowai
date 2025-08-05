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
        Schema::create('woocommerce_order_notifications', function (Blueprint $table) {
            $table->id('_id');
            $table->string('_uid')->unique();
            $table->unsignedBigInteger('woocommerce_orders__id');
            $table->unsignedInteger('vendors__id');
            $table->unsignedInteger('contacts__id')->nullable();
            $table->unsignedInteger('whatsapp_templates__id')->nullable();
            $table->string('notification_type');
            $table->string('status')->default('pending');
            $table->string('message_id')->nullable();
            $table->string('whatsapp_message_id')->nullable();
            $table->json('variables')->nullable();
            $table->json('response')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('__data')->nullable();
            $table->timestamps();

            $table->foreign('woocommerce_orders__id')->references('_id')->on('woocommerce_orders')->onDelete('cascade');
            $table->foreign('vendors__id')->references('_id')->on('vendors')->onDelete('cascade');
            $table->foreign('contacts__id')->references('_id')->on('contacts')->onDelete('set null');
            $table->foreign('whatsapp_templates__id')->references('_id')->on('whatsapp_templates')->onDelete('set null');
            $table->index(['vendors__id', 'status'], 'woocommerce_notifications_vendor_status');
            $table->index(['vendors__id', 'notification_type'], 'woocommerce_notifications_vendor_type');
            $table->index(['woocommerce_orders__id', 'notification_type'], 'woocommerce_notifications_order_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woocommerce_order_notifications');
    }
}; 