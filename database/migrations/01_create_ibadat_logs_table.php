<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Ibadat Logs Table
 * 
 * This table stores daily ibadat records for each user.
 * One record per user per day.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ibadat_logs', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to users table
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Date of the ibadat record (unique per user per day)
            $table->date('log_date');
            
            // Fasting tracking
            $table->boolean('roza_completed')->default(false);
            
            // Notes for the day
            $table->text('notes')->nullable();
            
            // Progress percentage (calculated field)
            $table->decimal('progress_percentage', 5, 2)->default(0);
            
            // Timestamps
            $table->timestamps();
            
            // Ensure one log per user per day
            $table->unique(['user_id', 'log_date']);
            
            // Index for faster queries
            $table->index(['user_id', 'log_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ibadat_logs');
    }
};
