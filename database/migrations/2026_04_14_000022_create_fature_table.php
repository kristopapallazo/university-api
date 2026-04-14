<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('FATURE', function (Blueprint $table) {
            $table->integerIncrements('FAT_ID');
            $table->date('FAT_DAT_LESHIM')->useCurrent();
            $table->decimal('FAT_SHUMA', 10, 2);        // must be > 0
            $table->string('FAT_STATUSI', 20)->default('E papaguar'); // 'E paguar', 'E papaguar', 'E vonuar'
            $table->string('FAT_PERSHKRIM', 200)->nullable();
            $table->unsignedInteger('STU_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('STU_ID')->references('STU_ID')->on('STUDENT');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('FATURE');
    }
};
