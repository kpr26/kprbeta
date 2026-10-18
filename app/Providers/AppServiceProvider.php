<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('users') && ! \Illuminate\Support\Facades\Schema::hasTable('daily_log_books')) {
                \Illuminate\Support\Facades\Schema::create('daily_log_books', function (\Illuminate\Database\Schema\Blueprint $table) {
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
        } catch (\Throwable $e) {
            // Abaikan jika database belum terhubung saat bootstrapping
        }
    }
}
