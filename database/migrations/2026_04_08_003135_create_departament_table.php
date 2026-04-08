<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DEPARTAMENT', function (Blueprint $table) {
            $table->integerIncrements('DEP_ID');
            $table->string('DEP_EM', 100)->unique();
            $table->unsignedInteger('FAK_ID');
            // PED_ID (head) FK to PEDAGOG — added via ALTER TABLE after PEDAGOG migration
            $table->unsignedInteger('PED_ID')->default(0);
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('FAK_ID')->references('FAK_ID')->on('FAKULTET');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DEPARTAMENT');
    }
};
