<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Family Members Table (Pivot)
 * 
 * This table stores the many-to-many relationship between users and families.
 * Tracks role (parent/child) within the family.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to families table
            $table->foreignId('family_id')
                  ->constrained('families')
                  ->onDelete('cascade');
            
            // Foreign key to users table
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Role in family: parent or child
            $table->enum('role', ['parent', 'child'])->default('child');
            
            // Join date
            $table->timestamp('joined_at')->useCurrent();
            
            // Is active member
            $table->boolean('is_active')->default(true);
            
            // Timestamps
            $table->timestamps();
            
            // One membership record per user per family
            $table->unique(['family_id', 'user_id']);
            
            // Index for faster queries
            $table->index(['family_id', 'role']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
