<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The search_indices migration defines `foreign_key`, but the legacy CakePHP DB
 * may have the column named `association_key`. The SearchIndex model now uses
 * `foreign_key`. This migration renames the column if it exists under the old name.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('search_indices', 'association_key') && !Schema::hasColumn('search_indices', 'foreign_key')) {
            Schema::table('search_indices', function (Blueprint $table) {
                $table->renameColumn('association_key', 'foreign_key');
            });
        }

        // Also fix timestamp columns if they use CakePHP-style names
        if (Schema::hasColumn('search_indices', 'created') && !Schema::hasColumn('search_indices', 'created_at')) {
            Schema::table('search_indices', function (Blueprint $table) {
                $table->renameColumn('created', 'created_at');
            });
        }
        if (Schema::hasColumn('search_indices', 'modified') && !Schema::hasColumn('search_indices', 'updated_at')) {
            Schema::table('search_indices', function (Blueprint $table) {
                $table->renameColumn('modified', 'updated_at');
            });
        }
    }

    public function down(): void
    {
        // Reversible but not critical
    }
};
