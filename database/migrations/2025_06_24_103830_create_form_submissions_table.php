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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->text('issue_description');
            $table->string('screenshot_url')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->string('submitter_name');
            $table->string('submitter_email');
            $table->string('page_url');
            $table->enum('status', ['new', 'reviewed', 'resolved'])->default('new');
            $table->timestamps();
            
            $table->index('form_id');
            $table->index('status');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
