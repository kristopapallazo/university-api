<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('STUDENT_PROGRAM', function (Blueprint $table) {
            $table->integerIncrements('STD_PRG_ID');
            $table->date('STD_PRG_DTP');                        // enrollment start date
            $table->date('STD_PRG_DTM')->default('2099-01-01'); // graduation date (2099 = not yet graduated)
            $table->string('STD_PRG_STATUS', 30)->default('Ne ndjekje');
            $table->unsignedInteger('STU_ID');
            $table->unsignedInteger('PROG_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('STU_ID')->references('STU_ID')->on('STUDENT');
            $table->foreign('PROG_ID')->references('PROG_ID')->on('PROGRAM_STUDIM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('STUDENT_PROGRAM');
    }
};
