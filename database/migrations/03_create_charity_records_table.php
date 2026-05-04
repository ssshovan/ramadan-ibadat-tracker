<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Charity Records Table
 * 
 * This table stores charity information for each ibadat log.
 * Tracks both amount donated and number of charitable acts.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('charity_records', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to ibadat_logs table
            $table->foreignId('ibadat_log_id')
                  ->constrained('ibadat_logs')
                  ->onDelete('cascade');
            
            // Charity amount in local currency
            $table->decimal('amount', 10, 2)->default(0);
            
            // Number of charitable acts performed
            $table->integer('acts_count')->default(0);
            
            // Description of charity acts
            $table->text('description')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // One charity record per ibadat log
            $table->unique('ibadat_log_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charity_records');
    }
};
