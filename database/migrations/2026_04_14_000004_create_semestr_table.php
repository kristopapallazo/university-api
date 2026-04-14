<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SEMESTR', function (Blueprint $table) {
            $table->integerIncrements('SEM_ID');
            $table->tinyInteger('SEM_NR');              // 1 or 2
            $table->date('SEM_DAT_FILLIMI');
            $table->date('SEM_DAT_MBARIMI');
            $table->unsignedInteger('VIT_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('VIT_ID')->references('VIT_ID')->on('VIT_AKADEMIK');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SEMESTR');
    }
};
