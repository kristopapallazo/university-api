<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('VIT_AKADEMIK', function (Blueprint $table) {
            $table->integerIncrements('VIT_ID');
            $table->string('VIT_EMER', 20);             // e.g. '2024-2025'
            $table->date('DATE_FILLIMI');
            $table->date('DATE_MBARIMI');               // must be > DATE_FILLIMI
            $table->boolean('AKTIV')->default(false);   // true = current year
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('VIT_AKADEMIK');
    }
};
