<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            // Browser and Device Information
            $table->string('browser_name')->nullable()->after('page_url');
            $table->string('browser_version')->nullable()->after('browser_name');
            $table->string('operating_system')->nullable()->after('browser_version');
            $table->string('device_type')->nullable()->after('operating_system');
            
            // Screen and Viewport
            $table->string('screen_resolution')->nullable()->after('device_type');
            $table->string('viewport_size')->nullable()->after('screen_resolution');
            
            // Additional technical metadata as JSON
            $table->json('technical_metadata')->nullable()->after('viewport_size');
            
            // Add indexes for common queries
            $table->index('browser_name');
            $table->index('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropIndex(['browser_name']);
            $table->dropIndex(['device_type']);
            
            $table->dropColumn([
                'browser_name',
                'browser_version',
                'operating_system',
                'device_type',
                'screen_resolution',
                'viewport_size',
                'technical_metadata'
            ]);
        });
    }
};