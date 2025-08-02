<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTriggerTypeToBotFlows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bot_flows', function (Blueprint $table) {
            // Add trigger_type field after start_trigger
            if (!Schema::hasColumn('bot_flows', 'trigger_type')) {
                $table->string('trigger_type')->default('is')->after('start_trigger');
            }
        });

        // Update existing bot flows to have default trigger type
        try {
            DB::table('bot_flows')->whereNull('trigger_type')->update(['trigger_type' => 'is']);
        } catch (\Exception $e) {
            // Column might not exist yet, that's okay
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bot_flows', function (Blueprint $table) {
            if (Schema::hasColumn('bot_flows', 'trigger_type')) {
                $table->dropColumn('trigger_type');
            }
        });
    }
}