<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('NOTA', function (Blueprint $table) {
            $table->integerIncrements('NOTA_ID');
            $table->decimal('NOTA_VLERA', 4, 2);        // 0–10
            $table->date('NOTA_DAT')->useCurrent();
            $table->unsignedInteger('STU_ID');
            $table->unsignedInteger('PROV_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['STU_ID', 'PROV_ID'], 'UK_NOTA');
            $table->foreign('STU_ID')->references('STU_ID')->on('STUDENT');
            $table->foreign('PROV_ID')->references('PROV_ID')->on('PROVIM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('NOTA');
    }
};
