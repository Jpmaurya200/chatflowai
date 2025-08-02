<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTypeColumnToWhatsappMessageLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatsapp_message_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_message_logs', 'type')) {
                $table->string('type')->nullable()->after('is_incoming_message');
                $table->index('type');
            }
        });

        // Populate the type column based on existing is_incoming_message values
        DB::statement("
            UPDATE whatsapp_message_logs 
            SET type = CASE 
                WHEN is_incoming_message = 1 THEN 'incoming'
                WHEN is_incoming_message = 0 THEN 'outgoing'
                ELSE 'unknown'
            END
            WHERE type IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatsapp_message_logs', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_message_logs', 'type')) {
                $table->dropIndex(['type']);
                $table->dropColumn('type');
            }
        });
    }
}
