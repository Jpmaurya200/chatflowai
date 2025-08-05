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
        Schema::create('woocommerce_integrations', function (Blueprint $table) {
            $table->id('_id');
            $table->string('_uid')->unique();
            $table->unsignedInteger('vendors__id');
            $table->string('site_url')->unique();
            $table->text('consumer_key');
            $table->text('consumer_secret');
            $table->json('webhook_ids')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('notification_types')->nullable();
            $table->string('webhook_url')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->json('settings')->nullable();
            $table->json('__data')->nullable();
            $table->timestamps();

            $table->foreign('vendors__id')->references('_id')->on('vendors')->onDelete('cascade');
            $table->index(['vendors__id', 'is_active']);
            $table->index('site_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woocommerce_integrations');
    }
}; 