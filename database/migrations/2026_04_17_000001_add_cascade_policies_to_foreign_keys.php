<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add onDelete cascade/setNull policies to foreign keys that lack them.
     *
     * Strategy:
     *  - cascade  → child record has no meaning without parent
     *  - set null → optional FK, child survives without parent
     *  - restrict → structural refs (default, no change needed)
     *
     * Skipped on SQLite — it does not support ALTER TABLE for foreign keys.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // ── FAKULTET.PED_ID  (dean) → set null ──
        Schema::table('FAKULTET', function (Blueprint $table) {
            $table->dropForeign('fk_fak_ped');
            $table->foreign('PED_ID', 'fk_fak_ped')
                ->references('PED_ID')->on('PEDAGOG')
                ->onDelete('set null');
        });

        // ── DEPARTAMENT.PED_ID  (head) → set null ──
        Schema::table('DEPARTAMENT', function (Blueprint $table) {
            $table->dropForeign('fk_dep_ped');
            $table->foreign('PED_ID', 'fk_dep_ped')
                ->references('PED_ID')->on('PEDAGOG')
                ->onDelete('set null');
        });

        // ── STUDENT.DHOM_ID → set null (student keeps record, loses room) ──
        Schema::table('STUDENT', function (Blueprint $table) {
            $table->dropForeign(['DHOM_ID']);
            $table->foreign('DHOM_ID')
                ->references('DHOM_ID')->on('DHOME')
                ->onDelete('set null');
        });

        // ── SEMESTR.VIT_ID → cascade ──
        Schema::table('SEMESTR', function (Blueprint $table) {
            $table->dropForeign(['VIT_ID']);
            $table->foreign('VIT_ID')
                ->references('VIT_ID')->on('VIT_AKADEMIK')
                ->onDelete('cascade');
        });

        // ── KURRIKULA → cascade on both FKs ──
        Schema::table('KURRIKULA', function (Blueprint $table) {
            $table->dropForeign(['PROG_ID']);
            $table->dropForeign(['LEND_ID']);
            $table->foreign('PROG_ID')
                ->references('PROG_ID')->on('PROGRAM_STUDIM')
                ->onDelete('cascade');
            $table->foreign('LEND_ID')
                ->references('LEND_ID')->on('LENDA')
                ->onDelete('cascade');
        });

        // ── STUDENT_PROGRAM → cascade on both FKs ──
        Schema::table('STUDENT_PROGRAM', function (Blueprint $table) {
            $table->dropForeign(['STU_ID']);
            $table->dropForeign(['PROG_ID']);
            $table->foreign('STU_ID')
                ->references('STU_ID')->on('STUDENT')
                ->onDelete('cascade');
            $table->foreign('PROG_ID')
                ->references('PROG_ID')->on('PROGRAM_STUDIM')
                ->onDelete('cascade');
        });

        // ── REGJISTRIM → cascade on both FKs ──
        Schema::table('REGJISTRIM', function (Blueprint $table) {
            $table->dropForeign(['STU_ID']);
            $table->dropForeign(['SEK_ID']);
            $table->foreign('STU_ID')
                ->references('STU_ID')->on('STUDENT')
                ->onDelete('cascade');
            $table->foreign('SEK_ID')
                ->references('SEK_ID')->on('SEKSION')
                ->onDelete('cascade');
        });

        // ── PROVIM.SEK_ID → cascade ──
        Schema::table('PROVIM', function (Blueprint $table) {
            $table->dropForeign(['SEK_ID']);
            $table->foreign('SEK_ID')
                ->references('SEK_ID')->on('SEKSION')
                ->onDelete('cascade');
        });

        // ── NOTA → cascade on both FKs ──
        Schema::table('NOTA', function (Blueprint $table) {
            $table->dropForeign(['STU_ID']);
            $table->dropForeign(['PROV_ID']);
            $table->foreign('STU_ID')
                ->references('STU_ID')->on('STUDENT')
                ->onDelete('cascade');
            $table->foreign('PROV_ID')
                ->references('PROV_ID')->on('PROVIM')
                ->onDelete('cascade');
        });

        // ── HUAZIM → cascade on STU_ID, restrict on LIBN_ID ──
        Schema::table('HUAZIM', function (Blueprint $table) {
            $table->dropForeign(['STU_ID']);
            $table->foreign('STU_ID')
                ->references('STU_ID')->on('STUDENT')
                ->onDelete('cascade');
            // LIBN_ID stays restrict — don't delete loan history when removing a book
        });

        // ── FATURE.STU_ID → cascade ──
        Schema::table('FATURE', function (Blueprint $table) {
            $table->dropForeign(['STU_ID']);
            $table->foreign('STU_ID')
                ->references('STU_ID')->on('STUDENT')
                ->onDelete('cascade');
        });

        // ── DHOME.KONV_ID → cascade ──
        Schema::table('DHOME', function (Blueprint $table) {
            $table->dropForeign(['KONV_ID']);
            $table->foreign('KONV_ID')
                ->references('KONV_ID')->on('KONVIKT')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Re-create FKs without onDelete (restores original RESTRICT default)

        Schema::table('FAKULTET', function (Blueprint $table) {
            $table->dropForeign('fk_fak_ped');
            $table->foreign('PED_ID', 'fk_fak_ped')
                ->references('PED_ID')->on('PEDAGOG');
        });

        Schema::table('DEPARTAMENT', function (Blueprint $table) {
            $table->dropForeign('fk_dep_ped');
            $table->foreign('PED_ID', 'fk_dep_ped')
                ->references('PED_ID')->on('PEDAGOG');
        });

        Schema::table('STUDENT', function (Blueprint $table) {
            $table->dropForeign(['DHOM_ID']);
            $table->foreign('DHOM_ID')
                ->references('DHOM_ID')->on('DHOME');
        });

        Schema::table('SEMESTR', function (Blueprint $table) {
            $table->dropForeign(['VIT_ID']);
            $table->foreign('VIT_ID')
                ->references('VIT_ID')->on('VIT_AKADEMIK');
        });

        Schema::table('KURRIKULA', function (Blueprint $table) {
            $table->dropForeign(['PROG_ID']);
            $table->dropForeign(['LEND_ID']);
            $table->foreign('PROG_ID')
                ->references('PROG_ID')->on('PROGRAM_STUDIM');
            $table->foreign('LEND_ID')
                ->references('LEND_ID')->on('LENDA');
        });

        Schema::table('STUDENT_PROGRAM', function (Blueprint $table) {
            $table->dropForeign(['STU_ID']);
            $table->dropForeign(['PROG_ID']);
            $table->foreign('STU_ID')
                ->references('STU_ID')->on('STUDENT');
            $table->foreign('PROG_ID')
                ->references('PROG_ID')->on('PROGRAM_STUDIM');
        });

        Schema::table('REGJISTRIM', function (Blueprint $table) {
            $table->dropForeign(['STU_ID']);
            $table->dropForeign(['SEK_ID']);
            $table->foreign('STU_ID')
                ->references('STU_ID')->on('STUDENT');
            $table->foreign('SEK_ID')
                ->references('SEK_ID')->on('SEKSION');
        });

        Schema::table('PROVIM', function (Blueprint $table) {
            $table->dropForeign(['SEK_ID']);
            $table->foreign('SEK_ID')
                ->references('SEK_ID')->on('SEKSION');
        });

        Schema::table('NOTA', function (Blueprint $table) {
            $table->dropForeign(['STU_ID']);
            $table->dropForeign(['PROV_ID']);
            $table->foreign('STU_ID')
                ->references('STU_ID')->on('STUDENT');
            $table->foreign('PROV_ID')
                ->references('PROV_ID')->on('PROVIM');
        });

        Schema::table('HUAZIM', function (Blueprint $table) {
            $table->dropForeign(['STU_ID']);
            $table->foreign('STU_ID')
                ->references('STU_ID')->on('STUDENT');
        });

        Schema::table('FATURE', function (Blueprint $table) {
            $table->dropForeign(['STU_ID']);
            $table->foreign('STU_ID')
                ->references('STU_ID')->on('STUDENT');
        });

        Schema::table('DHOME', function (Blueprint $table) {
            $table->dropForeign(['KONV_ID']);
            $table->foreign('KONV_ID')
                ->references('KONV_ID')->on('KONVIKT');
        });
    }
};
