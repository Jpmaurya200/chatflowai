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
        Schema::table('user_active_flows', function (Blueprint $table) {
            // Add __data column if it doesn't exist
            if (!Schema::hasColumn('user_active_flows', '__data')) {
                $table->json('__data')->nullable()->after('activated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_active_flows', function (Blueprint $table) {
            // Drop __data column if it exists
            if (Schema::hasColumn('user_active_flows', '__data')) {
                $table->dropColumn('__data');
            }
        });
    }
};
