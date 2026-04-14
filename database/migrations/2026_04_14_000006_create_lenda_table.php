<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LENDA', function (Blueprint $table) {
            $table->integerIncrements('LEND_ID');
            $table->string('LEND_EMER', 150);
            $table->string('LEND_KOD', 20)->unique();
            $table->unsignedInteger('DEP_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('DEP_ID')->references('DEP_ID')->on('DEPARTAMENT');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LENDA');
    }
};
