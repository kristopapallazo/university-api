<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix PED_ID columns that were created as NOT NULL DEFAULT 0
     * before the nullable() definition was added to the original migration.
     */
    public function up(): void
    {
        Schema::table('FAKULTET', function (Blueprint $table) {
            $table->unsignedInteger('PED_ID')->nullable()->default(null)->change();
        });

        Schema::table('DEPARTAMENT', function (Blueprint $table) {
            $table->unsignedInteger('PED_ID')->nullable()->default(null)->change();
        });

        // Fix any rows that have PED_ID = 0 (invalid FK reference)
        DB::table('FAKULTET')->where('PED_ID', 0)->update(['PED_ID' => null]);
        DB::table('DEPARTAMENT')->where('PED_ID', 0)->update(['PED_ID' => null]);
    }

    public function down(): void
    {
        Schema::table('FAKULTET', function (Blueprint $table) {
            $table->unsignedInteger('PED_ID')->nullable(false)->default(0)->change();
        });

        Schema::table('DEPARTAMENT', function (Blueprint $table) {
            $table->unsignedInteger('PED_ID')->nullable(false)->default(0)->change();
        });
    }
};
