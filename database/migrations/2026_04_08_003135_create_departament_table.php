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
            // Nullable: a department may exist before a head is assigned
            $table->unsignedInteger('PED_ID')->nullable()->default(null);
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
