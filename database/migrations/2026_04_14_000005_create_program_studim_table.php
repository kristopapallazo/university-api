<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PROGRAM_STUDIM', function (Blueprint $table) {
            $table->integerIncrements('PROG_ID');
            $table->string('PROG_EM', 150);
            $table->string('PROG_NIV', 15);             // 'Bachelor', 'Master', 'Doktorature'
            $table->integer('PROG_KRD');                // credits, must be > 0
            $table->unsignedInteger('DEP_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('DEP_ID')->references('DEP_ID')->on('DEPARTAMENT');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PROGRAM_STUDIM');
    }
};
