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
        Schema::create('shopify_integrations', function (Blueprint $table) {
            $table->id('_id');
            $table->string('_uid')->unique();
            $table->unsignedInteger('vendors__id');
            $table->string('shop_domain')->unique();
            $table->text('access_token');
            $table->string('webhook_id')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('notification_types')->nullable();
            $table->string('webhook_url')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->json('__data')->nullable();
            $table->timestamps();

            $table->foreign('vendors__id')->references('_id')->on('vendors')->onDelete('cascade');
            $table->index(['vendors__id', 'is_active']);
            $table->index('shop_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_integrations');
    }
};
