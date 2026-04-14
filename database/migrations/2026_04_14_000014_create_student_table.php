<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('STUDENT', function (Blueprint $table) {
            $table->integerIncrements('STU_ID');
            $table->string('STU_EM', 100);
            $table->string('STU_MB', 100);
            $table->string('STU_ATESI', 100)->nullable();
            $table->char('STU_GJINI', 1);               // 'M' or 'F'
            $table->date('STU_DTL');                     // date of birth (must be < 2010-01-01)
            $table->string('STU_NR_MATRIKULL', 20)->unique();
            $table->string('STU_EMAIL', 150)->unique(); // must end in @std.uamd.edu.al
            $table->string('STU_TEL', 15)->nullable();
            $table->date('STU_DAT_REGJISTRIM')->useCurrent();
            $table->string('STU_STATUS', 30)->default('Aktiv'); // 'Aktiv', 'Pezulluar', 'I diplomuar', 'Cregjistruar'
            $table->unsignedInteger('DHOM_ID')->nullable(); // dormitory room (optional)
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('DHOM_ID')->references('DHOM_ID')->on('DHOME');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('STUDENT');
    }
};
