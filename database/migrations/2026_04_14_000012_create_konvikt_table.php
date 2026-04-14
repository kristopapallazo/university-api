<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('KONVIKT', function (Blueprint $table) {
            $table->integerIncrements('KONV_ID');
            $table->string('KONV_EMER', 100);
            $table->string('KONV_ADRESE', 200);
            $table->integer('KONV_KAPACITET');          // must be > 0
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('KONVIKT');
    }
};
