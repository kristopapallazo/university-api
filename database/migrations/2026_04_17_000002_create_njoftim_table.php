<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('NJOFTIM', function (Blueprint $table) {
            $table->id('NJOF_ID');
            $table->unsignedBigInteger('USER_ID');
            $table->string('NJOF_TITULL', 200);
            $table->text('NJOF_TEKST');
            $table->enum('NJOF_TIPI', ['info', 'sukses', 'paralajmerim'])->default('info');
            $table->boolean('NJOF_IS_READ')->default(false);
            $table->timestamp('NJOF_READ_AT')->nullable();
            $table->unsignedBigInteger('SENT_BY_ADMIN_ID')->nullable();
            $table->timestamp('CREATED_AT')->nullable();
            $table->timestamp('UPDATED_AT')->nullable();

            $table->foreign('USER_ID')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('SENT_BY_ADMIN_ID')->references('id')->on('users')->onDelete('set null');

            $table->index('USER_ID');
            $table->index(['USER_ID', 'NJOF_IS_READ']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('NJOFTIM');
    }
};
