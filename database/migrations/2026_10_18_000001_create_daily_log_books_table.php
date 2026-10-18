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
        if (! Schema::hasTable('daily_log_books')) {
            Schema::create('daily_log_books', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('tanggal');
                $table->string('jam_mulai', 10);
                $table->string('jam_selesai', 10);
                $table->string('lokasi', 255);
                $table->enum('status', ['Done', 'Continue'])->default('Done');
                $table->text('deskripsi');
                $table->string('foto_awal');
                $table->string('foto_proses');
                $table->string('foto_akhir');
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['tanggal', 'user_id']);
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_log_books');
    }
};
