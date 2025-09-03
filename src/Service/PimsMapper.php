<?php
declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Main\PimsProdukt;
use App\Entity\Main\PimsPapier;
use App\Entity\Main\PimsDruckfarben;
use App\Entity\Main\PimsKaschierung;

final class PimsMapper
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PimsSkuParser $parser,
    ) {}

    /**
     * Liefert Arrays mit ['code' => ..., 'bezeichnung' => ...] oder null
     */
    public function resolveCodesForItem(string $oxartnum): array
    {
        $tokens = $this->parser->parse($oxartnum);

        // Indexe aufbauen (bei Bedarf später cachen)
        [$prodIdx, $paperIdx, $colorIdx, $kasoIdx] = [
            $this->buildIndex($this->em->getRepository(PimsProdukt::class)->findAll()),
            $this->buildIndex($this->em->getRepository(PimsPapier::class)->findAll()),
            $this->buildIndex($this->em->getRepository(PimsDruckfarben::class)->findAll()),
            $this->buildIndex($this->em->getRepository(PimsKaschierung::class)->findAll()),
        ];

        // Farbe: DRUxx → exakte Matches + Mehrfachwerte + (nur) Wildcard, wenn DRU im SKU vorkommt
        $color = $this->matchByTokens($colorIdx, $tokens, ['DRU']);

        // Kaschierung/Veredelung: VEAxx / KASxx / LACxx
        $kasch = $this->matchByTokens($kasoIdx, $tokens, ['VEA']);

        // Papier: über DIN/FUL/MEC/RAD/PAP
        $paper = $this->matchByTokens($paperIdx, $tokens, ['DIN','FUL','MEC','RAD']);

        // Produkt: via ORD (Wildcard, wenn ORD im SKU), sowie PRO/PRD/ART/REG/LBS
        $product = $this->matchByTokens($prodIdx, $tokens, ['ORD','REG','LBS']);

        return [
            'product'      => $product,      // ['code'=>..., 'bezeichnung'=>...] oder null
            'paper'        => $paper,
            'color'        => $color,
            'wvkaschieren' => $kasch,
        ];
    }

    /**
     * Index je Mapping-Key:
     * [
     *   'DRU' => [
     *      'values'   => ['20' => <entity>, '10' => <entity>, ...],
     *      'wildcard' => <entity>|null  // für {"DRU":"-"} oder {"DRU":"*"}
     *   ],
     *   ...
     * ]
     *
     * Unterstützt:
     * - Objekt: {"DRU":"20"}  oder {"DRU":"20,10"}  oder {"DRU":"20|10"}
     * - Array:  [{"DRU":"20"},{"DRU":"10"}]
     */
    private function buildIndex(array $rows): array
    {
        $index = [];

        foreach ($rows as $row) {
            $json = $row->getEasymapping();
            if (!$json) continue;

            $decoded = json_decode($json, true);
            if ($decoded === null && preg_match('/}\s*,\s*{/', $json)) {
                // Sonderfall: mehrere JSON-Objekte durch Komma getrennt, aber nicht als Array gekapselt
                $decoded = json_decode('['.$json.']', true);
            }
            if ($decoded === null) {
                continue; // ungültiges JSON weiterhin überspringen
            }

            // In uniforme Paare umwandeln
            $pairs = [];
            if (is_array($decoded) && self::isAssoc($decoded)) {
                foreach ($decoded as $k => $v) {
                    $pairs[] = [$k, $v];
                }
            } elseif (is_array($decoded)) {
                foreach ($decoded as $obj) {
                    if (is_array($obj)) {
                        foreach ($obj as $k => $v) {
                            $pairs[] = [$k, $v];
                        }
                    }
                }
            } else {
                continue;
            }

            foreach ($pairs as [$key, $valRaw]) {
                $index[$key] ??= ['values' => [], 'wildcard' => null];

                // Mehrfachwerte in Strings: "20,10" oder "20|10"
                $vals = is_string($valRaw) ? preg_split('/[|,]/', $valRaw) : (array)$valRaw;

                foreach ($vals as $singleValRaw) {
                    $singleVal = is_string($singleValRaw) ? trim($singleValRaw) : (string)$singleValRaw;

                    if ($singleVal === '-' || $singleVal === '*') {
                        // Wildcard je Key (first wins)
                        if ($index[$key]['wildcard'] === null) {
                            $index[$key]['wildcard'] = $row;
                        }
                        continue;
                    }

                    // numerisch normalisieren
                    $valNorm = preg_match('/^\d+$/', $singleVal) ? ltrim($singleVal, '0') : $singleVal;
                    if ($valNorm === '') $valNorm = '0';

                    // first wins
                    $index[$key]['values'] += [$valNorm => $row];
                }
            }
        }

        return $index;
    }

    /**
     * Sucht über Keys in Reihenfolge:
     * 1) Exakter Match (nur wenn der Token existiert)
     * 2) Wildcard-Match NUR, wenn der Token-Key im SKU auch existiert (Schutz gegen "REG" kapert Papier)
     *
     * Rückgabe: ['code'=>..., 'bezeichnung'=>...] oder null.
     */
    private function matchByTokens(array $index, array $tokens, array $keys): ?array
    {
        foreach ($keys as $k) {
            if (!isset($index[$k])) continue;

            $tokenExists = array_key_exists($k, $tokens);

            // 1) Exakter Match
            if ($tokenExists) {
                $val = (string)$tokens[$k];
                $valNorm = preg_match('/^\d+$/', $val) ? ltrim($val, '0') : $val;
                if ($valNorm === '') $valNorm = '0';

                if (isset($index[$k]['values'][$valNorm])) {
                    $e = $index[$k]['values'][$valNorm];
                    return [
                        'code'        => method_exists($e, 'getCode') ? $e->getCode() : null,
                        'bezeichnung' => method_exists($e, 'getBezeichnung') ? $e->getBezeichnung() : null,
                    ];
                }
            }

            // 2) Wildcard NUR wenn Token vorhanden (verhindert REG→Papier)
            if ($tokenExists && $index[$k]['wildcard']) {
                $e = $index[$k]['wildcard'];
                return [
                    'code'        => method_exists($e, 'getCode') ? $e->getCode() : null,
                    'bezeichnung' => method_exists($e, 'getBezeichnung') ? $e->getBezeichnung() : null,
                ];
            }
        }

        return null;
    }

    private static function isAssoc(array $arr): bool
    {
        foreach (array_keys($arr) as $k) {
            if (!is_int($k)) return true;
        }
        return false;
    }
}
