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
        Schema::create('cta_button_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cta_button_id')->constrained()->onDelete('cascade');
            $table->string('url_pattern', 500);
            $table->string('destination_url', 500);
            $table->string('pattern_description')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('cta_button_id');
            $table->index('is_active');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cta_button_rules');
    }
};
