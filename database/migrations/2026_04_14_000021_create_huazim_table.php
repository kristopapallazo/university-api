<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('HUAZIM', function (Blueprint $table) {
            $table->integerIncrements('HUAZ_ID');
            $table->date('HUAZ_DAT_MARRE')->useCurrent();
            $table->date('HUAZ_DAT_KTHIM');             // expected return date
            $table->date('HUAZ_DAT_KTHYER')->nullable(); // actual return date (null = not yet returned)
            $table->unsignedInteger('STU_ID');
            $table->unsignedInteger('LIBN_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('STU_ID')->references('STU_ID')->on('STUDENT');
            $table->foreign('LIBN_ID')->references('LIBN_ID')->on('LIBN');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('HUAZIM');
    }
};
