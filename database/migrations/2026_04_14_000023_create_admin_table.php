<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ADMIN', function (Blueprint $table) {
            $table->integerIncrements('ADM_ID');
            $table->string('ADM_EM', 50);
            $table->string('ADM_MB', 50);
            $table->string('ADM_EMAIL', 100)->unique();
            $table->string('ADM_POZICION', 100)->nullable();
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ADMIN');
    }
};
