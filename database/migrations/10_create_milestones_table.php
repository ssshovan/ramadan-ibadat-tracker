<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Milestones/Badges Table
 * 
 * This table stores user achievements and badges earned.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to users table
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Milestone type
            $table->enum('type', [
                'prayer_streak_7',
                'prayer_streak_14',
                'prayer_streak_21',
                'prayer_streak_30',
                'fasting_streak_7',
                'fasting_streak_14',
                'fasting_streak_21',
                'fasting_streak_30',
                'quran_streak_7',
                'quran_streak_14',
                'quran_streak_21',
                'quran_streak_30',
                'charity_streak_7',
                'charity_streak_14',
                'charity_streak_21',
                'charity_streak_30',
                'complete_ramadan',
                'perfect_day',
                'family_contributor'
            ]);
            
            // Milestone name
            $table->string('name');
            
            // Description
            $table->text('description')->nullable();
            
            // Icon/Badge image
            $table->string('icon')->nullable();
            
            // Date earned
            $table->timestamp('earned_at')->useCurrent();
            
            // Timestamps
            $table->timestamps();
            
            // One milestone per user per type
            $table->unique(['user_id', 'type']);
            
            // Index for faster queries
            $table->index(['user_id', 'earned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
