<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('FAKULTET', function (Blueprint $table) {
            $table->integerIncrements('FAK_ID');
            $table->string('FAK_EM', 100)->unique();
            // PED_ID (dean) FK to PEDAGOG — added via ALTER TABLE after PEDAGOG migration
            $table->unsignedInteger('PED_ID')->default(0);
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('FAKULTET');
    }
};
