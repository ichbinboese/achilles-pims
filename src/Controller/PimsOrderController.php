<?php

namespace App\Controller;

use App\Entity\Main\PimsKaschierung;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Main\PimsPapier;
use App\Entity\Main\PimsProdukt;
use App\Entity\Main\PimsDruckfarben;
use App\Entity\Main\EasyOrder;
use App\Entity\Main\EasyProduct;
use App\Repository\EasyProductRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PimsOrderController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
    ) {}

    /* ============ ORDER ============ */
    #[Route('/api/proxy/pims-order', name: 'proxy_pims_order', methods: ['POST'])]
    public function proxyPimsOrder(Request $request): JsonResponse
    {
        try {
            $apiBase = $this->sanitize($_ENV['PIMS_API_BASE'] ?? 'https://pims-api.stage.printdays.net/v1');
            $apiKey  = $this->sanitize($_ENV['PIMS_API_KEY']  ?? '');
            $authHdr = $this->sanitize($_ENV['PIMS_API_AUTH'] ?? '');

            if ($apiKey === '' || $authHdr === '') {
                return new JsonResponse(['error' => 'Proxy-Fehler: PIMS_API_KEY oder PIMS_API_AUTH fehlt.'], 500);
            }

            // --- Felder aus dem Request lesen
            $fields = [];
            foreach ($request->request->all() as $k => $v) {
                $fields[$k] = is_scalar($v) ? (string)$v : json_encode($v);
            }

            // --- Country zuverlässig auf exakt "deutschland" erzwingen (API-Akzeptanz)
            $c = strtolower(trim($fields['country'] ?? ''));
            $map = ['deutschland' => 'deutschland', 'germany' => 'deutschland', 'de' => 'deutschland', 'ger' => 'deutschland'];
            $fields['country'] = $map[$c] ?? 'deutschland';

            // (Optional) locale absichern
            if (!isset($fields['locale']) || trim($fields['locale']) === '') {
                $fields['locale'] = 'Celle';
            }

            // --- Dateien ergänzen
            foreach ($request->files->all() as $k => $file) {
                if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    $fields[$k] = DataPart::fromPath(
                        $file->getPathname(),
                        $file->getClientOriginalName(),
                        $file->getMimeType()
                    );
                } elseif (is_array($file)) {
                    foreach ($file as $idx => $f) {
                        if ($f instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                            $fields["{$k}[{$idx}]"] = DataPart::fromPath(
                                $f->getPathname(),
                                $f->getClientOriginalName(),
                                $f->getMimeType()
                            );
                        }
                    }
                }
            }

            // --- Multipart bauen
            $formData = new FormDataPart($fields);

            // Content-Type inkl. boundary extrahieren
            $ctLine = 'multipart/form-data';
            foreach ($formData->getPreparedHeaders()->toArray() as $line) {
                if (stripos($line, 'Content-Type:') === 0) {
                    $ctLine = trim(substr($line, strlen('Content-Type:')));
                    break;
                }
            }

            $headers = [
                'Content-Type' => $ctLine,
                'Accept'       => 'application/json',
                'Authorization'=> $authHdr,
                'User-Agent'   => 'Achilles-Pinguin/1.0', // wie im erfolgreichen curl
            ];

            // --- Debug: rausgehende Felder (ohne Dateien) loggen
            $this->logger->info('[PIMS DEBUG] Order-Request', [
                'url'         => rtrim($apiBase, '/') . '/pimsOrder.php?output=json&key=' . $apiKey,
                'contentType' => $ctLine,
                'fields'      => array_map(static fn($v) => $v instanceof DataPart ? '[file]' : $v, $fields),
            ]);

            // --- Request senden (HTTP/1.1)
            $response = $this->client->request('POST', rtrim($apiBase, '/') . '/pimsOrder.php', [
                'query'        => ['output' => 'json', 'key' => $apiKey],
                'headers'      => $headers,
                'body'         => $formData->toIterable(),
                'http_version' => '1.1',
            ]);

            return new JsonResponse($response->toArray(false), $response->getStatusCode());
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Proxy-Fehler bei Bestellung: ' . $e->getMessage()], 500);
        }
    }

    /* ============ PRODUCT ============ */
    #[Route('/api/proxy/pims-product', name: 'pims_proxy_product', methods: ['POST'])]
    public function proxyPimsProduct(Request $request): JsonResponse
    {
        try {
            $apiBase = $this->sanitize($_ENV['PIMS_API_BASE'] ?? 'https://pims-api.stage.printdays.net/v1');
            $apiKey  = $this->sanitize($_ENV['PIMS_API_KEY']  ?? '');
            $authHdr = $this->sanitize($_ENV['PIMS_API_AUTH'] ?? '');

            if ($apiKey === '' || $authHdr === '') {
                return new JsonResponse(['error' => 'Proxy-Fehler: PIMS_API_KEY oder PIMS_API_AUTH fehlt.'], 500);
            }

            [$headers, $body] = $this->buildMultipart($request, $authHdr);

            $response = $this->client->request('POST', rtrim($apiBase, '/') . '/pimsProduct.php', [
                'query'        => ['output' => 'json', 'key' => $apiKey],
                'headers'      => $headers,
                'body'         => $body,
                'http_version' => '1.1',
            ]);

            return new JsonResponse($response->toArray(false), $response->getStatusCode());
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Proxy-Fehler bei Produktanlage: ' . $e->getMessage()], 500);
        }
    }

    /* ============ PARCEL ============ */
    #[Route('/api/proxy/pims-parcel', name: 'proxy_pims_parcel', methods: ['POST'])]
    public function proxyPimsParcel(Request $request): JsonResponse
    {
        try {
            // Hole die API-Daten aus den Umgebungsvariablen
            $apiBase = $this->sanitize($_ENV['PIMS_API_BASE'] ?? 'https://pims-api.stage.printdays.net/v1');
            $apiKey  = $this->sanitize($_ENV['PIMS_API_KEY']  ?? '');
            $authHdr = $this->sanitize($_ENV['PIMS_API_AUTH'] ?? '');

            // Überprüfe, ob API-Schlüssel und Auth-Header vorhanden sind
            if ($apiKey === '' || $authHdr === '') {
                return new JsonResponse(['error' => 'Proxy-Fehler: PIMS_API_KEY oder PIMS_API_AUTH fehlt.'], 500);
            }

            // Initialisiere die Felder
            $fields = [];

            // Baue den Multipart-Body, um die Anfrage an die PIMS-API zu senden
            [$headers, $body] = $this->buildMultipart($request, $authHdr);

            // Debug-Ausgabe, um sicherzustellen, dass die Daten korrekt übergeben werden
            dump($body);  // Dies gibt dir die Anfrage zurück, die an die PIMS-API gesendet wird

            // Sende die Anfrage an die PIMS-API
            $response = $this->client->request('POST', rtrim($apiBase, '/') . '/pimsParcel.php', [
                'query'        => ['output' => 'json', 'key' => $apiKey],
                'headers'      => $headers,
                'body'         => $body,
                'http_version' => '1.1',
            ]);

            // Rückgabe der Antwort von PIMS-API
            return new JsonResponse($response->toArray(false), $response->getStatusCode());
        } catch (\Throwable $e) {
            // Fehlerbehandlung, wenn etwas schief geht
            return new JsonResponse(['error' => 'Proxy-Fehler bei Versandanfrage: ' . $e->getMessage()], 500);
        }
    }


    /* ============ Helpers ============ */

    /** entfernt BOM/Zero-Width/CR/LF/Nichtdruckbares und trimmt */
    private function sanitize(?string $v): string
    {
        $v = $v ?? '';
        $v = preg_replace('/\x{FEFF}|\x{200B}|\x{200C}|\x{200D}|\x{2060}/u', '', $v);
        $v = str_replace(["\r", "\n"], '', $v);
        $v = preg_replace('/[^\x20-\x7E]/', '', $v);
        return trim($v);
    }

    /**
     * Baut multipart/form-data (Header ASSOZIATIV inkl. boundary + iterable Body).
     * @return array{0: array<string,string|string[]>, 1: iterable}
     */
    private function buildMultipart(Request $request, string $authorizationHeader): array
    {
        $fields = [];

        // normale Felder
        foreach ($request->request->all() as $k => $v) {
            $fields[$k] = is_scalar($v) ? (string) $v : json_encode($v);
        }

        // Dateien (falls vorhanden)
        foreach ($request->files->all() as $k => $file) {
            if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                $fields[$k] = DataPart::fromPath(
                    $file->getPathname(),
                    $file->getClientOriginalName(),
                    $file->getMimeType()
                );
            } elseif (is_array($file)) {
                foreach ($file as $idx => $f) {
                    if ($f instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                        $fields["{$k}[{$idx}]"] = DataPart::fromPath(
                            $f->getPathname(),
                            $f->getClientOriginalName(),
                            $f->getMimeType()
                        );
                    }
                }
            }
        }

        $formData = new FormDataPart($fields);

        // Content-Type (mit boundary) aus PreparedHeaders EXPLIZIT holen
        // toArray() liefert "Content-Type: multipart/…; boundary=…", wir extrahieren nur den Wert
        $ctLine = null;
        foreach ($formData->getPreparedHeaders()->toArray() as $line) {
            if (stripos($line, 'Content-Type:') === 0) {
                $ctLine = trim(substr($line, strlen('Content-Type:')));
                break;
            }
        }
        if (!$ctLine) {
            // Fallback (sollte nicht nötig sein)
            $ctLine = 'multipart/form-data';
        }

        // ASSOZIATIVE Header – so mag es der HttpClient am liebsten
        $headers = [
            'Content-Type' => $ctLine,            // inkl. boundary=...
            'Accept'       => 'application/json, application/xml', // wie Doku
            'Authorization'=> $authorizationHeader,
            'User-Agent'   => 'Achilles-Pinguin/1.0 (+https://achilles.de)', // optional, hilfreich
        ];

        return [$headers, $formData->toIterable()];
    }



    #[Route('/api/pims-produkt', name: 'api_pims_produkt', methods: ['GET'])]
    public function getProdukte(EntityManagerInterface $em): JsonResponse
    {
        $produkte = $em->getRepository(PimsProdukt::class)->findAll();
        return $this->json($produkte);
    }

    #[Route('/api/pims-papier', name: 'api_pims_papier', methods: ['GET'])]
    public function getPapier(EntityManagerInterface $em): JsonResponse
    {
        $papier = $em->getRepository(PimsPapier::class)->findAll();
        return $this->json($papier);
    }

    #[Route('/api/pims-druckfarben', name: 'api_pims_druckfarben', methods: ['GET'])]
    public function getDruckfarben(EntityManagerInterface $em): JsonResponse
    {
        $farben = $em->getRepository(PimsDruckfarben::class)->findBy([], ['bezeichnung' => 'ASC']);
        return $this->json($farben);
    }

    #[Route('/api/pims-kaschierung', name: 'api_pims_kaschierung', methods: ['GET'])]
    public function getKasschierung(EntityManagerInterface $em): JsonResponse
    {
        $kaschierung = $em->getRepository(PimsKaschierung::class)->findAll();
        return $this->json($kaschierung);
    }

    #[Route('/api/save-order', name: 'save_order', methods: ['POST'])]
    public function saveOrder(Request $request)
    {
        // Hole die Daten aus der Anfrage
        $data = json_decode($request->getContent(), true);
        dump($data);

        // Validierung der empfangenen Daten
        if (!isset($data['orderid'], $data['ordernr'], $data['productid'], $data['productnr'], $data['oxordernr'], $data['ddposition'])) {
            return new JsonResponse(['error' => 'Fehlende Daten'], 400);
        }


        // Speichern der Order in EasyOrder
        $easyOrder = new EasyOrder();
        $easyOrder->setOrderid($data['orderid']);
        $easyOrder->setOrdernr($data['ordernr']);
        $this->entityManager->persist($easyOrder);
        $this->entityManager->flush();

        // Speichern des Produkts in EasyProduct
        $easyProduct = new EasyProduct();
        $easyProduct->setProductid($data['productid']);
        $easyProduct->setProductnr($data['productnr']);
        $easyProduct->setOxordernr($data['oxordernr']);
        $easyProduct->setDdposition($data['ddposition']);
        $easyProduct->setOrder($easyOrder); // Verknüpft das Produkt mit der Bestellung
        $this->entityManager->persist($easyProduct);
        $this->entityManager->flush();

        // Erfolgsantwort zurückgeben
        return new JsonResponse(['success' => true], 200);
    }
    /**
     * Proxy: Einzelner Order-Status
     * /api/proxy/pims-order-status?orderid=12345
     */
    #[Route('/api/proxy/pims-order-status', name: 'proxy_pims_order_status_single', methods: ['GET'])]
    public function proxyPimsOrderStatusSingle(Request $request): JsonResponse
    {
        try {
            $apiBase = $this->sanitize($_ENV['PIMS_API_BASE'] ?? 'https://pims-api.stage.printdays.net/v1');
            $apiKey  = $this->sanitize($_ENV['PIMS_API_KEY']  ?? '');
            $authHdr = $this->sanitize($_ENV['PIMS_API_AUTH'] ?? '');

            if ($apiKey === '' || $authHdr === '') {
                return new JsonResponse(['error' => 'Proxy-Fehler: PIMS_API_KEY oder PIMS_API_AUTH fehlt.'], 500);
            }

            $orderId = $this->sanitize($request->query->get('orderid') ?? '');
            if ($orderId === '') {
                return new JsonResponse(['error' => 'orderid ist erforderlich'], 400);
            }

            $response = $this->client->request('GET', rtrim($apiBase, '/') . '/getOrderStatus.php', [
                'query'        => ['orderid' => $orderId, 'output' => 'json', 'key' => $apiKey],
                'headers'      => [
                    'Accept'        => 'application/json, application/xml',
                    'Authorization' => $authHdr,
                    'User-Agent'    => 'Achilles-Pinguin/1.0',
                ],
                'http_version' => '1.1',
            ]);

            return new JsonResponse($response->toArray(false), $response->getStatusCode());
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Proxy-Fehler bei Order-Status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Proxy: Batch-Order-Status
     * POST /api/proxy/pims-order-status/batch
     * Body: { "orderids": ["82001821","82001824", ...] }
     */
    #[Route('/api/proxy/pims-order-status/batch', name: 'proxy_pims_order_status_batch', methods: ['POST'])]
    public function proxyPimsOrderStatusBatch(Request $request): JsonResponse
    {
        try {
            $apiBase = $this->sanitize($_ENV['PIMS_API_BASE'] ?? 'https://pims-api.stage.printdays.net/v1');
            $apiKey  = $this->sanitize($_ENV['PIMS_API_KEY']  ?? '');
            $authHdr = $this->sanitize($_ENV['PIMS_API_AUTH'] ?? '');

            if ($apiKey === '' || $authHdr === '') {
                return new JsonResponse(['error' => 'PIMS_API_KEY oder PIMS_API_AUTH fehlt'], 500);
            }

            // JSON einlesen (auch falls Content-Type fehlt/abweicht)
            $raw = $request->getContent() ?: '{}';
            $payload = json_decode($raw, true);
            if (!is_array($payload)) {
                return new JsonResponse(['error' => 'Ungültiges JSON', 'raw' => $raw], 400);
            }

            // Sowohl orderids als auch orderIds akzeptieren
            $ids = $payload['orderids'] ?? $payload['orderIds'] ?? null;
            if (!is_array($ids)) {
                return new JsonResponse(['error' => 'orderids (Array) ist erforderlich', 'payload' => $payload], 400);
            }

            // Normalisieren & leere rauswerfen
            $ids = array_values(array_filter(array_map(fn($v) => $this->sanitize((string)$v), $ids)));
            if (count($ids) === 0) {
                return new JsonResponse(['error' => 'orderids ist leer'], 400);
            }

            // Requests parallel
            $requests = [];
            foreach ($ids as $id) {
                $requests[$id] = $this->client->request('GET', rtrim($apiBase, '/') . '/getOrderStatus.php', [
                    'query'        => ['orderid' => $id, 'output' => 'json', 'key' => $apiKey],
                    'headers'      => [
                        'Accept'        => 'application/json, application/xml',
                        'Authorization' => $authHdr,
                        'User-Agent'    => 'Achilles-Pinguin/1.0',
                    ],
                    'http_version' => '1.1',
                ]);
            }

            $items = [];
            foreach ($requests as $orderId => $resp) {
                try {
                    $data = $resp->toArray(false);

                    $productNr = null;
                    $productStatus = null;
                    if (isset($data['products']['product']) && is_array($data['products']['product']) && count($data['products']['product']) > 0) {
                        $first = $data['products']['product'][0];
                        $productNr = $first['productnr'] ?? null;
                        $productStatus = $first['status'] ?? null;
                    }

                    $items[] = [
                        'orderId'       => $orderId,
                        'orderNr'       => $data['ordernr'] ?? null,
                        'status'        => $data['status'] ?? null,
                        'productNr'     => $productNr,
                        'productStatus' => $productStatus,
                    ];
                } catch (\Throwable $e) {
                    $items[] = [
                        'orderId' => $orderId,
                        'error'   => true,
                        'message' => $e->getMessage(),
                    ];
                }
            }

            return new JsonResponse(['items' => $items], 200);

        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Proxy-Fehler bei Batch-Order-Status: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/proxy/pims-product-storno', name: 'api_pims_product_storno', methods: ['POST'])]
    public function stornoProduct(Request $request, HttpClientInterface $http): JsonResponse
    {
        $payload = json_decode($request->getContent() ?: '{}', true) ?? [];
        $productId = $payload['productid'] ?? null;
        if (!$productId) {
            return $this->json(['success' => 0, 'error' => 'productid erforderlich'], 400);
        }

        $base = rtrim($_ENV['PIMS_API_BASE'] ?? 'https://pims-api.stage.printdays.net/v1', '/');
        $key  = $_ENV['PIMS_API_KEY']  ?? '';
        $auth = $_ENV['PIMS_API_AUTH'] ?? '';

        // Authorization-Header normalisieren
        $authHeader = (static function (string $raw): string {
            $raw = trim($raw);
            if ($raw === '') return '';
            if (stripos($raw, 'basic ') === 0) return $raw;                 // "Basic xxx"
            if (str_contains($raw, ':')) return 'Basic '.base64_encode($raw); // "user:pass"
            return 'Basic '.$raw;                                            // nur Base64
        })($auth);

        try {
            // stornoProduct.php (laut deinem Wunsch)
            $response = $http->request('POST', $base.'/stornoProduct.php', [
                'query'        => ['output' => 'json', 'key' => $key],
                'headers'      => [
                    'Accept'        => 'application/json, application/xml',
                    'Authorization' => $authHeader,
                    'User-Agent'    => 'Achilles-Pinguin/1.0',
                ],
                'body'         => ['productid' => (string)$productId], // x-www-form-urlencoded
                'http_version' => '1.1',
                'timeout'      => 20,
            ]);

            $status = $response->getStatusCode();
            $raw    = $response->getContent(false) ?? '';
            $data   = json_decode($raw, true);

            if ($data === null) {
                $ct = $response->getHeaders(false)['content-type'][0] ?? '';
                if (stripos($ct, 'xml') !== false || str_starts_with(ltrim($raw), '<')) {
                    $xml = @simplexml_load_string($raw);
                    if ($xml) $data = json_decode(json_encode($xml), true);
                }
            }

            if (!$data || !is_array($data)) {
                $data = ['success' => 0, 'error' => 'Unlesbare Antwort vom PIMS-Server', 'raw' => mb_substr($raw, 0, 1000)];
            }

            // Debug-Metadaten beilegen
            $headers = $response->getHeaders(false);
            $ct = $headers['content-type'][0] ?? '';
            $data = $data + ['http_status' => $status, 'content_type' => $ct];

            return $this->json($data, $status);
        } catch (\Throwable $e) {
            return $this->json(['success' => 0, 'error' => 'Transportfehler: '.$e->getMessage()], 502);
        }
    }

    public function stornoProductBatch(Request $request, HttpClientInterface $http): JsonResponse
    {
        $payload = json_decode($request->getContent() ?: '{}', true) ?? [];
        $ids = $payload['productids'] ?? $payload['productIds'] ?? null;
        if (!is_array($ids) || empty($ids)) {
            return $this->json(['success' => 0, 'error' => 'productids (Array) erforderlich'], 400);
        }

        $results = [];
        foreach ($ids as $pid) {
            $sub = $this->stornoProduct(new Request(content: json_encode(['productid' => $pid])), $http);
            $results[] = json_decode($sub->getContent(), true);
        }
        return $this->json(['items' => $results], 200);
    }

    #[Route('/api/proxy/pims-storno', name: 'api_pims_storno', methods: ['POST'])]
    public function stornoOrder(Request $request, HttpClientInterface $http): JsonResponse
    {
        $body = json_decode($request->getContent() ?: '{}', true) ?? [];
        $orderId = $body['orderid'] ?? null;

        if (!$orderId) {
            return $this->json(['success' => 0, 'error' => 'orderid erforderlich'], 400);
        }

        $base = rtrim($_ENV['PIMS_API_BASE'] ?? 'https://pims-api.stage.printdays.net/v1', '/');
        $key  = $_ENV['PIMS_API_KEY']  ?? '';
        $auth = $_ENV['PIMS_API_AUTH'] ?? '';

        // Hilfsfunktion: Authorization-Header normalisieren (user:pass | base64 | "Basic ...")
        $authHeader = (static function (string $raw): string {
            $raw = trim($raw);
            if ($raw === '') return '';
            if (stripos($raw, 'basic ') === 0) return $raw;          // schon fertig
            if (str_contains($raw, ':')) return 'Basic '.base64_encode($raw); // user:pass
            return 'Basic '.$raw;                                     // base64 ohne "Basic "
        })($auth);

        try {
            // 1) POST (x-www-form-urlencoded), output/key in QUERY (wie bei dir bei Order/Product)
            $response = $http->request('POST', $base.'/stornoOrder.php', [
                'query'        => ['output' => 'json', 'key' => $key],
                'headers'      => [
                    'Accept'        => 'application/json, application/xml',
                    'Authorization' => $authHeader,
                    'User-Agent'    => 'Achilles-Pinguin/1.0',
                    // KEIN Content-Type setzen => HttpClient macht urlencoded aus 'body'
                ],
                'body'         => [
                    'orderid' => (string)$orderId,
                ],
                'http_version' => '1.1',
                'timeout'      => 20,
            ]);

            $status = $response->getStatusCode();

            // 403-Fallback: einige Backends verlangen GET für stornoOrder.php
            if ($status === 403) {
                $response = $http->request('GET', $base.'/stornoOrder.php', [
                    'query'        => [
                        'orderid' => (string)$orderId,
                        'output'  => 'json',
                        'key'     => $key,
                    ],
                    'headers'      => [
                        'Accept'        => 'application/json, application/xml',
                        'Authorization' => $authHeader,
                        'User-Agent'    => 'Achilles-Pinguin/1.0',
                    ],
                    'http_version' => '1.1',
                    'timeout'      => 20,
                ]);
                $status = $response->getStatusCode();
            }

            $raw  = $response->getContent(false) ?? '';
            $data = json_decode($raw, true);

            if ($data === null) {
                $ct = $response->getHeaders(false)['content-type'][0] ?? '';
                if (stripos($ct, 'xml') !== false || str_starts_with(ltrim($raw), '<')) {
                    $xml = @simplexml_load_string($raw);
                    if ($xml) $data = json_decode(json_encode($xml), true);
                }
            }

            $headers = $response->getHeaders(false);
            $ct = $headers['content-type'][0] ?? '';
            $debug = [
                'http_status' => $status,
                'content_type'=> $ct,
            ];

            if (!$data || !is_array($data)) {
                $data = [
                        'success' => 0,
                        'error'   => 'Leere oder unlesbare Antwort vom PIMS-Server',
                        'raw'     => mb_substr($raw, 0, 1000),
                    ] + $debug;
            } else {
                // nützliche Debug-Metadaten mitgeben
                $data = $data + $debug;
            }

            return $this->json($data, $status);
        } catch (\Throwable $e) {
            return $this->json(['success' => 0, 'error' => 'Transportfehler: '.$e->getMessage()], 502);
        }
    }
}
