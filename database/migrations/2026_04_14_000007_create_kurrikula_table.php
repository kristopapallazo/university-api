<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('KURRIKULA', function (Blueprint $table) {
            $table->integerIncrements('KURR_ID');
            $table->tinyInteger('KURR_VIT');            // 1–5 (year of study)
            $table->tinyInteger('KURR_NR_SEMESTER');    // 1 or 2
            $table->tinyInteger('KURR_KREDIT');         // 2–12
            $table->boolean('KURR_I_DETYRUESHEM')->default(true); // true = mandatory
            $table->unsignedInteger('PROG_ID');
            $table->unsignedInteger('LEND_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('PROG_ID')->references('PROG_ID')->on('PROGRAM_STUDIM');
            $table->foreign('LEND_ID')->references('LEND_ID')->on('LENDA');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('KURRIKULA');
    }
};
