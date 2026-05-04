<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Prayers Table
 * 
 * This table stores prayer completion status for each ibadat log.
 * 5 prayers per day: Fajr, Dhuhr, Asr, Maghrib, Isha
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prayers', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to ibadat_logs table
            $table->foreignId('ibadat_log_id')
                  ->constrained('ibadat_logs')
                  ->onDelete('cascade');
            
            // Prayer name (Fajr, Dhuhr, Asr, Maghrib, Isha)
            $table->enum('prayer_name', ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha']);
            
            // Prayer completion status
            $table->boolean('is_completed')->default(false);
            
            // Prayer time (when it was completed)
            $table->time('completed_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Ensure one record per prayer per log
            $table->unique(['ibadat_log_id', 'prayer_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayers');
    }
};
