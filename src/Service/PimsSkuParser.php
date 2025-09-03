<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Zerlegt die oxartnum in Tokens.
 * - Prefix vor dem ersten '-' (z. B. "ORD") → tokens['ORD'] = '-' (für Wildcard-Matches)
 * - Standard-Blöcke KEY + DIGITS (z. B. DRU20, KAS00, GRI10, VEA30, MEC2, RAD00 ...)
 * - Spezial: "DINA4" → zusätzlich tokens['DIN'] = '4' (Alias, damit Mappings auf DIN greifen)
 * - Führende Nullen werden entfernt (z. B. '00' -> '0')
 */
final class PimsSkuParser
{
    public function parse(string $oxartnum): array
    {
        $tokens = [];

        // 1) Prefix vor dem ersten '-' als Familienkennung (z. B. "ORD")
        if (preg_match('/^([A-Z]{3,})-/', $oxartnum, $m)) {
            $prefix = $m[1];
            $tokens[$prefix] = '-';           // bewusste Wildcard, damit {"ORD":"-"} greifen kann
            $tokens['_prefix'] = $prefix;     // optionales Debug/Info
        }

        // 2) KEY + DIGITS (1–3 Ziffern, damit MEC2 auch greift)
        if (preg_match_all('/([A-Z]{3,4})(\d{1,3})/', $oxartnum, $m, PREG_SET_ORDER)) {
            foreach ($m as $hit) {
                $key = $hit[1];               // z. B. DRU / KAS / GRI / DINA / MEC / RAD ...
                $val = ltrim($hit[2], '0');   // "21" statt "021"
                if ($val === '') $val = '0';
                $tokens[$key] = $val;

                // Spezialfall DIN: "DINA4" → zusätzlich "DIN" = "4"
                if (str_starts_with($key, 'DIN') && strlen($key) > 3) {
                    $tokens['DIN'] = $val;
                }
            }
        }

        return $tokens;
    }
}
