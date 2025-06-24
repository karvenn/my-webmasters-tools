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
        Schema::table('forms', function (Blueprint $table) {
            // Active status
            $table->boolean('is_active')->default(true)->after('embed_token');
            
            // Button customization
            $table->string('button_color', 7)->default('#3b82f6')->after('is_active');
            $table->string('button_text_color', 7)->default('#ffffff')->after('button_color');
            $table->enum('button_size', ['small', 'medium', 'large'])->default('medium')->after('button_text_color');
            $table->enum('button_position', ['bottom-right', 'bottom-left', 'top-right', 'top-left'])->default('bottom-right')->after('button_size');
            $table->string('button_text', 50)->default('Report Issue')->after('button_position');
            
            // Domain validation
            $table->json('allowed_domains')->nullable()->after('button_text');
            
            // Add indexes
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            
            $table->dropColumn([
                'is_active',
                'button_color',
                'button_text_color',
                'button_size',
                'button_position',
                'button_text',
                'allowed_domains'
            ]);
        });
    }
};