<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The artist doesn't paint portraits, so the seeded Realizam description
 * shouldn't mention them. Only the untouched seeded text is replaced.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('painting_styles')
            ->where('description', 'Prizori, portreti i motivi naslikani do detalja.')
            ->update(['description' => 'Prizori, gradovi i motivi naslikani do detalja.']);
    }

    public function down(): void
    {
        // Nothing to restore.
    }
};
