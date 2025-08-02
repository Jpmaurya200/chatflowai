<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlowManagementTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create bot_flows table if it doesn't exist
        if (!Schema::hasTable('bot_flows')) {
            Schema::create('bot_flows', function (Blueprint $table) {
                $table->id('_id');
                $table->string('_uid')->unique();
                $table->string('title');
                $table->string('start_trigger')->nullable();
                $table->unsignedBigInteger('vendors__id');
                $table->string('status')->default('active');
                $table->json('__data')->nullable();
                $table->timestamps();
            });
        }

        // Add WhatsApp fields to bot_flows table
        Schema::table('bot_flows', function (Blueprint $table) {
            if (!Schema::hasColumn('bot_flows', 'whatsapp_flow_id')) {
                $table->string('whatsapp_flow_id')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('bot_flows', 'whatsapp_sync_status')) {
                $table->string('whatsapp_sync_status')->default('pending')->after('whatsapp_flow_id');
            }
            
            if (!Schema::hasColumn('bot_flows', 'whatsapp_sync_at')) {
                $table->timestamp('whatsapp_sync_at')->nullable()->after('whatsapp_sync_status');
            }
            
            if (!Schema::hasColumn('bot_flows', 'whatsapp_meta_data')) {
                $table->json('whatsapp_meta_data')->nullable()->after('whatsapp_sync_at');
            }

            // Add indexes
            $table->index('whatsapp_flow_id');
            $table->index('whatsapp_sync_status');
        });

        // Create new table to track active flows per user
        Schema::create('user_active_flows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('flow_id');
            $table->string('phone_number'); // Store user's phone number for WhatsApp identification
            $table->timestamp('activated_at');
            $table->json('__data')->nullable(); // Store flow state and step data
            $table->timestamps();

            // Add indexes and foreign keys
            $table->index('phone_number');
            $table->unique(['user_id', 'phone_number']); // One active flow per user per phone number
            
            // Add foreign key constraints if your database uses them
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('flow_id')->references('id')->on('flows')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the active flows tracking table
        Schema::dropIfExists('user_active_flows');

        // Remove WhatsApp fields from bot_flows table or drop the table entirely
        if (Schema::hasTable('bot_flows')) {
            // Check if this migration created the table by checking if it has our WhatsApp columns
            $hasWhatsAppColumns = Schema::hasColumn('bot_flows', 'whatsapp_flow_id');
            
            if ($hasWhatsAppColumns) {
                Schema::table('bot_flows', function (Blueprint $table) {
                    // Drop indexes first (only if they exist)
                    try {
                        $table->dropIndex(['whatsapp_flow_id']);
                    } catch (\Exception $e) {
                        // Index doesn't exist, continue
                    }
                    
                    try {
                        $table->dropIndex(['whatsapp_sync_status']);
                    } catch (\Exception $e) {
                        // Index doesn't exist, continue
                    }

                    // Drop columns (only if they exist)
                    if (Schema::hasColumn('bot_flows', 'whatsapp_flow_id')) {
                        $table->dropColumn('whatsapp_flow_id');
                    }
                    if (Schema::hasColumn('bot_flows', 'whatsapp_sync_status')) {
                        $table->dropColumn('whatsapp_sync_status');
                    }
                    if (Schema::hasColumn('bot_flows', 'whatsapp_sync_at')) {
                        $table->dropColumn('whatsapp_sync_at');
                    }
                    if (Schema::hasColumn('bot_flows', 'whatsapp_meta_data')) {
                        $table->dropColumn('whatsapp_meta_data');
                    }
                });
            }
            
            // If the table only has basic columns, it means we created it, so drop it
            $columns = Schema::getColumnListing('bot_flows');
            if (count($columns) <= 8) { // Basic columns we created
                Schema::dropIfExists('bot_flows');
            }
        }
    }
}
