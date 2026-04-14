<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('REGJISTRIM', function (Blueprint $table) {
            $table->integerIncrements('REGJ_ID');
            $table->date('DAT_REGJ')->useCurrent();
            $table->string('REGJ_STATUS', 30)->default('Mungoi'); // 'Kaloi', 'Nuk Kaloi', 'Mungoi'
            $table->decimal('PIK_1', 5, 2)->default(0);           // 0–400
            $table->decimal('PIK_2', 5, 2)->default(0);           // 0–500
            $table->decimal('PIK_3', 5, 2)->default(0);           // 0–100
            $table->decimal('PIK_TOTAL', 7, 2)->storedAs('PIK_1 + PIK_2 + PIK_3');
            $table->unsignedInteger('SEK_ID');
            $table->unsignedInteger('STU_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['STU_ID', 'SEK_ID'], 'UK_STU_SEK');
            $table->foreign('SEK_ID')->references('SEK_ID')->on('SEKSION');
            $table->foreign('STU_ID')->references('STU_ID')->on('STUDENT');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('REGJISTRIM');
    }
};
