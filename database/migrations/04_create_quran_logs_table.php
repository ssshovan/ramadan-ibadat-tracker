<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Quran Logs Table
 * 
 * This table stores Quran recitation progress for each ibadat log.
 * Tracks pages read and surahs completed.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quran_logs', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to ibadat_logs table
            $table->foreignId('ibadat_log_id')
                  ->constrained('ibadat_logs')
                  ->onDelete('cascade');
            
            // Number of pages read
            $table->integer('pages_read')->default(0);
            
            // Starting surah (optional tracking)
            $table->string('start_surah')->nullable();
            
            // Ending surah (optional tracking)
            $table->string('end_surah')->nullable();
            
            // Juz completed (optional)
            $table->integer('juz_completed')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // One Quran log per ibadat log
            $table->unique('ibadat_log_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quran_logs');
    }
};
