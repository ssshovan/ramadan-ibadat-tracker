<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Streaks Table
 * 
 * This table stores streak information for each user.
 * Separate streaks for: prayer, fasting, quran, charity
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('streaks', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to users table
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Streak type: prayer, fasting, quran, charity
            $table->enum('streak_type', ['prayer', 'fasting', 'quran', 'charity']);
            
            // Current streak count
            $table->integer('current_streak')->default(0);
            
            // Longest streak ever achieved
            $table->integer('longest_streak')->default(0);
            
            // Last date streak was updated
            $table->date('last_updated')->nullable();
            
            // Date when streak started
            $table->date('streak_start_date')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // One streak record per user per type
            $table->unique(['user_id', 'streak_type']);
            
            // Index for faster queries
            $table->index(['user_id', 'streak_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streaks');
    }
};
