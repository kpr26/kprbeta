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
        Schema::table('whatsapp_notification_settings', function (Blueprint $table) {
            $table->json('target_groups')->nullable()->after('target_group_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_notification_settings', function (Blueprint $table) {
            $table->dropColumn('target_groups');
        });
    }
};
