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
        Schema::create('cta_buttons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('button_name');
            $table->string('embed_token')->unique();
            $table->boolean('is_active')->default(true);
            $table->json('allowed_domains')->nullable();
            $table->string('button_color')->default('#3b82f6');
            $table->string('button_text_color')->default('#ffffff');
            $table->string('button_size')->default('medium');
            $table->string('button_position')->default('bottom-right');
            $table->string('button_text')->default('Learn More');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('embed_token');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cta_buttons');
    }
};
