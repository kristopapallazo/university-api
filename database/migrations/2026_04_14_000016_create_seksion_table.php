<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SEKSION', function (Blueprint $table) {
            $table->integerIncrements('SEK_ID');
            $table->string('DITA', 10);                 // 'Hene','Marte','Merkure','Enjte','Premte','Shtune','Diele'
            $table->time('ORE_FILLIMI');
            $table->time('ORE_MBARIMI');                // must be > ORE_FILLIMI
            $table->unsignedInteger('LEND_ID');
            $table->unsignedInteger('PED_ID');
            $table->unsignedInteger('PROG_ID');
            $table->unsignedInteger('SEM_ID');
            $table->unsignedInteger('SALL_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('LEND_ID')->references('LEND_ID')->on('LENDA');
            $table->foreign('PED_ID')->references('PED_ID')->on('PEDAGOG');
            $table->foreign('PROG_ID')->references('PROG_ID')->on('PROGRAM_STUDIM');
            $table->foreign('SEM_ID')->references('SEM_ID')->on('SEMESTR');
            $table->foreign('SALL_ID')->references('SALL_ID')->on('AUDITOR');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SEKSION');
    }
};
