<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Families Table
 * 
 * This table stores family information.
 * Each family has a unique code for joining.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            
            // Family name
            $table->string('name');
            
            // Unique family code for joining
            $table->string('family_code')->unique();
            
            // Description (optional)
            $table->text('description')->nullable();
            
            // Creator/Admin of the family
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Family streak (collective streak)
            $table->integer('family_streak')->default(0);
            
            // Timestamps
            $table->timestamps();
            
            // Index for faster lookup by code
            $table->index('family_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
