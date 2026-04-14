<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SALLE', function (Blueprint $table) {
            $table->integerIncrements('SALLE_ID');
            $table->string('SALLE_NR', 20)->unique();
            $table->tinyInteger('SALLE_KAPACITET');     // must be > 0
            $table->char('SALLE_LLOJ', 1);             // 'A' = auditor/lab, 'Z' = office
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SALLE');
    }
};
