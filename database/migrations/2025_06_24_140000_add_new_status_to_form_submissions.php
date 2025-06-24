<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to drop and recreate the constraint
        if (DB::getDriverName() === 'pgsql') {
            // Drop the old constraint
            DB::statement('ALTER TABLE form_submissions DROP CONSTRAINT IF EXISTS form_submissions_status_check');
            
            // Add the new constraint with 'new' included
            DB::statement("ALTER TABLE form_submissions ADD CONSTRAINT form_submissions_status_check CHECK (status IN ('new', 'wip', 'agency_review', 'client_review', 'on_hold', 'concluded'))");
        } else {
            // For MySQL and others, modify the column
            Schema::table('form_submissions', function (Blueprint $table) {
                $table->enum('status', ['new', 'wip', 'agency_review', 'client_review', 'on_hold', 'concluded'])
                    ->default('new')
                    ->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // Drop the new constraint
            DB::statement('ALTER TABLE form_submissions DROP CONSTRAINT IF EXISTS form_submissions_status_check');
            
            // Add back the previous constraint
            DB::statement("ALTER TABLE form_submissions ADD CONSTRAINT form_submissions_status_check CHECK (status IN ('wip', 'agency_review', 'client_review', 'on_hold', 'concluded'))");
        } else {
            Schema::table('form_submissions', function (Blueprint $table) {
                $table->enum('status', ['wip', 'agency_review', 'client_review', 'on_hold', 'concluded'])
                    ->default('wip')
                    ->change();
            });
        }
    }
};