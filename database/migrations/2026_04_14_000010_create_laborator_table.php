<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LABORATOR', function (Blueprint $table) {
            // PK is also the FK to AUDITOR (table-per-type pattern)
            $table->unsignedInteger('SALLE_ID')->primary();
            $table->tinyInteger('LAB_PC_NR')->default(0);           // number of PCs
            $table->string('LAB_PAJISJE', 255)->default('Standart'); // equipment description
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('SALLE_ID')->references('SALL_ID')->on('AUDITOR')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LABORATOR');
    }
};
