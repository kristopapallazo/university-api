<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PEDAGOG', function (Blueprint $table) {
            $table->integerIncrements('PED_ID');
            $table->string('PED_EM', 50);
            $table->string('PED_MB', 50);
            $table->char('PED_GJINI', 1);               // 'M' or 'F'
            $table->string('PED_TITULLI', 20)->default('Msc.'); // 'Prof. Dr.', 'Dr.', 'Msc.', 'Doc.', 'Prof. As. Dr.'
            $table->string('PED_EMAIL', 100)->unique(); // must end in @uamd.edu.al
            $table->string('PED_TEL', 15)->nullable();
            $table->date('PED_DTL')->nullable();         // date of birth
            $table->date('PED_DT_PUNESIM')->nullable();  // hire date
            $table->unsignedInteger('DEP_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('DEP_ID')->references('DEP_ID')->on('DEPARTAMENT');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PEDAGOG');
    }
};
