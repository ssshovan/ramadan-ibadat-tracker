<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Notes Table
 * 
 * This table stores additional notes for ibadat logs.
 * Supports multiple notes per day with categories.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to ibadat_logs table
            $table->foreignId('ibadat_log_id')
                  ->constrained('ibadat_logs')
                  ->onDelete('cascade');
            
            // Note category
            $table->enum('category', ['general', 'prayer', 'quran', 'charity', 'reflection'])
                  ->default('general');
            
            // Note content
            $table->text('content');
            
            // Is favorite/important note
            $table->boolean('is_important')->default(false);
            
            // Timestamps
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['ibadat_log_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
