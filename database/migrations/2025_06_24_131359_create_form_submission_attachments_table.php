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
        Schema::create('form_submission_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->integer('size');
            $table->string('url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submission_attachments');
    }
};