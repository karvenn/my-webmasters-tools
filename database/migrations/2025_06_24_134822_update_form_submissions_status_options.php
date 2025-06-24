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
            
            // Update existing values
            DB::table('form_submissions')
                ->where('status', 'new')
                ->update(['status' => 'wip']);
                
            DB::table('form_submissions')
                ->where('status', 'reviewed')
                ->update(['status' => 'agency_review']);
                
            DB::table('form_submissions')
                ->where('status', 'resolved')
                ->update(['status' => 'concluded']);
            
            // Add the new constraint
            DB::statement("ALTER TABLE form_submissions ADD CONSTRAINT form_submissions_status_check CHECK (status IN ('wip', 'agency_review', 'client_review', 'on_hold', 'concluded'))");
        } else {
            // For MySQL and others, modify the column
            Schema::table('form_submissions', function (Blueprint $table) {
                $table->enum('status', ['wip', 'agency_review', 'client_review', 'on_hold', 'concluded'])
                    ->default('wip')
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
            
            // Revert status values
            DB::table('form_submissions')
                ->where('status', 'wip')
                ->update(['status' => 'new']);
                
            DB::table('form_submissions')
                ->where('status', 'agency_review')
                ->update(['status' => 'reviewed']);
                
            DB::table('form_submissions')
                ->where('status', 'concluded')
                ->update(['status' => 'resolved']);
                
            // Set other statuses to a valid old status
            DB::table('form_submissions')
                ->whereIn('status', ['client_review', 'on_hold'])
                ->update(['status' => 'reviewed']);
            
            // Add the old constraint
            DB::statement("ALTER TABLE form_submissions ADD CONSTRAINT form_submissions_status_check CHECK (status IN ('new', 'reviewed', 'resolved'))");
        } else {
            Schema::table('form_submissions', function (Blueprint $table) {
                $table->enum('status', ['new', 'reviewed', 'resolved'])
                    ->default('new')
                    ->change();
            });
        }
    }
};