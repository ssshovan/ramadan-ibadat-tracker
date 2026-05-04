<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add Profile Fields to Users Table
 * 
 * Extends the default users table with additional profile information.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Profile picture
            $table->string('avatar')->nullable()->after('email');
            
            // Phone number
            $table->string('phone')->nullable()->after('avatar');
            
            // Date of birth
            $table->date('date_of_birth')->nullable()->after('phone');
            
            // Gender
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            
            // Bio/About
            $table->text('bio')->nullable()->after('gender');
            
            // Preferred language
            $table->string('language')->default('en')->after('bio');
            
            // Timezone
            $table->string('timezone')->default('UTC')->after('language');
            
            // Email notification preference
            $table->boolean('email_notifications')->default(true)->after('timezone');
            
            // Account is active
            $table->boolean('is_active')->default(true)->after('email_notifications');
            
            // Last login timestamp
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'phone',
                'date_of_birth',
                'gender',
                'bio',
                'language',
                'timezone',
                'email_notifications',
                'is_active',
                'last_login_at'
            ]);
        });
    }
};
