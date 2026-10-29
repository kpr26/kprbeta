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
        Schema::create('whatsapp_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('target_group_id')->nullable();
            $table->string('target_group_name')->nullable();
            $table->json('target_phone_numbers')->nullable();
            $table->string('scheduled_time', 10)->default('18:00');
            $table->boolean('is_active')->default(true);
            $table->boolean('send_to_group')->default(true);
            $table->boolean('send_to_numbers')->default(true);
            $table->text('header_template')->nullable();
            $table->text('reminder_template')->nullable();
            $table->text('success_template')->nullable();
            $table->text('footer_template')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notification_settings');
    }
};
