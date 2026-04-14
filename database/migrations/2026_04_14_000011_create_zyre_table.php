<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ZYRE', function (Blueprint $table) {
            // PK is also the FK to SALLE (table-per-type pattern)
            $table->unsignedInteger('SALL_ID')->primary();
            $table->string('ZYR_NR', 10);
            $table->unsignedInteger('PED_ID')->unique(); // one office per pedagogue
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('SALL_ID')->references('SALLE_ID')->on('SALLE')->onDelete('cascade');
            $table->foreign('PED_ID')->references('PED_ID')->on('PEDAGOG');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ZYRE');
    }
};
