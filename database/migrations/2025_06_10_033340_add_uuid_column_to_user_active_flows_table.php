<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUUIDColumnToUserActiveFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_active_flows', function (Blueprint $table) {
            // Add current_node_uid column after flow_id
            if (!Schema::hasColumn('user_active_flows', 'current_node_uid')) {
                $table->string('current_node_uid')->nullable()->after('flow_id');
            }

            // Add next_node_uid column after current_node_uid
            if (!Schema::hasColumn('user_active_flows', 'next_node_uid')) {
                $table->string('next_node_uid')->nullable()->after('current_node_uid');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_active_flows', function (Blueprint $table) {
            if (Schema::hasColumn('user_active_flows', 'next_node_uid')) {
                $table->dropColumn('next_node_uid');
            }
            if (Schema::hasColumn('user_active_flows', 'current_node_uid')) {
                $table->dropColumn('current_node_uid');
            }
        });
    }
}
