<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PROVIM', function (Blueprint $table) {
            $table->integerIncrements('PROV_ID');
            $table->string('TIP_EMER', 20);             // 'Midterm', 'Final', 'Vjeshte'
            $table->date('DAT_PROVIM');
            $table->unsignedInteger('SEK_ID');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('SEK_ID')->references('SEK_ID')->on('SEKSION');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PROVIM');
    }
};
