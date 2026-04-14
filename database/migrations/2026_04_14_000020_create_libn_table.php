<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LIBN', function (Blueprint $table) {
            $table->integerIncrements('LIBN_ID');
            $table->string('LIBN_TITULLI', 200);
            $table->string('LIBN_AUTORI', 100);
            $table->string('LIBN_ISBN', 20)->nullable()->unique();
            $table->year('LIBN_VITI')->nullable();
            $table->string('LIBN_STATUSI', 20)->default('Disponueshem'); // 'Disponueshem', 'Huazuar', 'Humbur'
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LIBN');
    }
};
