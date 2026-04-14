<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AUDITOR', function (Blueprint $table) {
            // PK is also the FK to SALLE (table-per-type pattern)
            $table->unsignedInteger('SALL_ID')->primary();
            $table->integer('AUD_Y')->default(0);       // floor/level
            $table->char('AUD_TIP', 1)->default('X');  // 'L' = lab, 'X' = standard lecture hall
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('SALL_ID')->references('SALLE_ID')->on('SALLE')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AUDITOR');
    }
};
