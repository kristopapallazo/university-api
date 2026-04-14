<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Resolve circular dependency: FAKULTET.PED_ID → PEDAGOG (dean)
        Schema::table('FAKULTET', function (Blueprint $table) {
            $table->foreign('PED_ID', 'fk_fak_ped')->references('PED_ID')->on('PEDAGOG');
        });

        // Resolve circular dependency: DEPARTAMENT.PED_ID → PEDAGOG (head)
        Schema::table('DEPARTAMENT', function (Blueprint $table) {
            $table->foreign('PED_ID', 'fk_dep_ped')->references('PED_ID')->on('PEDAGOG');
        });
    }

    public function down(): void
    {
        Schema::table('FAKULTET', function (Blueprint $table) {
            $table->dropForeign('fk_fak_ped');
        });

        Schema::table('DEPARTAMENT', function (Blueprint $table) {
            $table->dropForeign('fk_dep_ped');
        });
    }
};
