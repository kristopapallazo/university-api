<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DHOME', function (Blueprint $table) {
            $table->integerIncrements('DHOM_ID');
            $table->string('DHOM_NR', 20);
            $table->tinyInteger('DHOM_KAPACITET');      // must be > 0
            $table->unsignedInteger('KONV_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('KONV_ID')->references('KONV_ID')->on('KONVIKT');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DHOME');
    }
};
