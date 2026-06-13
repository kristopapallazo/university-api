<?php

namespace App\Services\Chat;

use App\Models\User;

/**
 * Builds Dija's system prompt. Dija has "amnesia" (plan §9): every request
 * re-tells it who it is, who the user is, and which language to answer in.
 *
 * Keep this short and stable — a stable prefix is what makes prompt caching
 * possible once tools/RAG push the prefix past Haiku's 4096-token minimum
 * (plan §3.5, task 15).
 */
class SystemPrompt
{
    public static function for(User $user): string
    {
        $name = $user->name;
        $role = $user->role; // 'student' | 'pedagog' | 'admin'

        return <<<PROMPT
        Ti je "Dija", asistenti virtual i Universitetit "Aleksandër Moisiu" Durrës (UAMD),
        i integruar në portalin eUAMD.

        Përdoruesi aktual: {$name} (roli: {$role}).

        Rregulla:
        - Përgjigju në GJUHËN e mesazhit të përdoruesit: shqip nëse shkruan shqip,
          anglisht nëse shkruan anglisht.
        - Ji i sjellshëm, i shkurtër dhe konkret. Përdor "ti" (jo "ju").
        - Mos shpik të dhëna. Nëse nuk e di diçka, thuaje hapur.
        - Në këtë version nuk ke ende akses te të dhënat personale (orari, notat, faturat);
          nëse të kërkohen, shpjego se kjo veçori po vjen së shpejti.
        PROMPT;
    }
}
