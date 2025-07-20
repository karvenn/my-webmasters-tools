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
        Schema::create('cta_button_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cta_button_id')->constrained()->onDelete('cascade');
            $table->foreignId('cta_button_rule_id')->nullable()->constrained()->onDelete('set null');
            $table->string('page_url', 500);
            $table->string('referrer_domain')->nullable();
            $table->integer('click_count')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('cta_button_id');
            $table->index('cta_button_rule_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cta_button_analytics');
    }
};
