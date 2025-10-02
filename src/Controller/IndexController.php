<?php

namespace App\Controller;

use App\Entity\Main\Bestellungen;
use App\Entity\Main\EasyOrder;
use App\Entity\Main\EasyProduct;
use App\Entity\Main\APPOrder;

use App\Repository\APPOrderRepository;
use App\Repository\APPProductRepository;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\AuthTokenRepository;
use App\Repository\EasyProductRepository;

class IndexController extends AbstractController
{
    private EntityManagerInterface $oracleManager;

    public function __construct(ManagerRegistry $registry)
    {
        $this->oracleManager = $registry->getManager('oracle');
        return null;
        return $this->json([
            'ok' => false,
            'message' => 'Unbekannter Fehler aufgetreten.'
        ]);
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/api/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): void
    {
        throw new \LogicException('Intercepted by the firewall.');
    }

    #[Route('/api/bestellung', name: 'api_bestellung', methods: ['GET'])]
    public function getBestellung(Request $request): JsonResponse
    {
        $fiNr = (int)$request->query->get('fiNr');
        $bestnr = $request->query->get('bestnr');

        $daten = $this->findBestellungMitText($fiNr, $bestnr);
        return $this->json($daten);
    }

    public function findBestellungMitText(int $fiNr, string $bestnr): array
    {
        $conn = $this->oracleManager->getConnection();

        $sql = <<<SQL
SELECT
  a.FI_NR    AS A_FI_NR,
  a.BESTNR   AS A_BESTNR,
  a.BESTPOS  AS A_BESTPOS,
  b.TXTLONG  AS B_TXTLONG,
  c.AUFNR    AS C_AUFNR
FROM E110 a
LEFT JOIN B4000 b
  ON b.FI_NR = a.FI_NR
 AND b.TXT_NR = a.TXT_NR
 AND b.TXT_ART = 'TQP'
LEFT JOIN E1101 c
  ON c.BESTNR  = a.BESTNR
 AND c.BESTPOS = a.BESTPOS
WHERE a.FI_NR   = :fiNr
  AND a.BESTNR  = :bestnr
ORDER BY a.BESTPOS
SQL;

        $stmt   = $conn->prepare($sql);
        $result = $stmt->executeQuery(['fiNr' => $fiNr, 'bestnr' => $bestnr]);

        return array_map(static function(array $row) {
            return [
                'fiNr'    => $row['A_FI_NR'],
                'bestnr'  => $row['A_BESTNR'],
                'bestpos' => $row['A_BESTPOS'],
                'txtlong' => $row['B_TXTLONG'] ?? null,
                'aufnr'   => $row['C_AUFNR'] ?? null,
            ];
        }, $result->fetchAllAssociative());
    }

    #[Route('/api/pims-bestellungen', name: 'pims_bestellungen_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $repository = $em->getRepository(Bestellungen::class);

        // Leere Kriterien [], aber sortieren nach den beiden Feldern:
        $bestellungen = $repository->findBy([], [
            'appbestellnummer' => 'ASC',
            'appbestellposition' => 'ASC'
        ]);

        return $this->json($bestellungen);
    }

    #[Route('/api/pims-bestellungen', name: 'pims_bestellungen_create', methods: ['POST'])]
    public function create(
        Request                $request,
        EntityManagerInterface $em,
        ValidatorInterface     $validator,
        AuthTokenRepository    $authTokenRepository
    ): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->json(['error' => 'Kein Auth-Token'], 401);
        }
        $token = substr($authHeader, 7);

        // Token aus DB holen und prüfen
        $authToken = $authTokenRepository->findOneBy(['token' => $token]);
        if (!$authToken) {
            return $this->json(['error' => 'Ungültiger Token'], 403);
        }
        if ($authToken->getExpiresAt() && $authToken->getExpiresAt() < new \DateTime()) {
            return $this->json(['error' => 'Token abgelaufen'], 403);
        }

        // --- Rest deiner Logik ---
        $data = json_decode($request->getContent(), true);

        $bestellung = new Bestellungen();
        $bestellung->setAppbestellnummer($data['appbestellnummer'] ?? '');
        $bestellung->setAppbestellposition((int)($data['appbestellposition'] ?? 0));
        $bestellung->setPimsid($data['pimsid'] ?? '');
        $bestellung->setPimsbestellnummer($data['pimsbestellnummer'] ?? '');
        $bestellung->setAppfirma($data['appfirma'] ?? '');

        $errors = $validator->validate($bestellung);

        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return $this->json(['error' => 'Validierung fehlgeschlagen', 'details' => $messages], 400);
        }

        $em->persist($bestellung);
        $em->flush();

        return $this->json(['status' => 'created', 'id' => $bestellung->getId()]);
    }

    #[Route('/api/bestellung/parse', name: 'api_bestellung_parse', methods: ['GET'])]
    public function parseBestellung(Request $request): JsonResponse
    {
        $fiNr   = (int)$request->query->get('fiNr');
        $bestnr = $request->query->get('bestnr');

        $daten = $this->findBestellungMitText($fiNr, $bestnr);
        if (!$daten || empty($daten)) {
            return $this->json(['ok' => false, 'error' => 'Keine Daten gefunden'], 404);
        }

        $client = HttpClient::create([
            'headers' => [
                'Authorization' => 'Bearer ' . ($_ENV['OPENAI_API_KEY'] ?? ''),
                'Content-Type'  => 'application/json',
            ],
            'timeout' => 60,
        ]);

        $model = $_ENV['OPENAI_MODEL'] ?? 'gpt-4o-mini';

        $objects = [];

        foreach ($daten as $row) {
            $txtlong = (string)($row['txtlong'] ?? '');
            $pos     = (string)($row['bestpos'] ?? '');
            $bnr     = (string)($row['bestnr'] ?? $bestnr ?? '');
            if (trim($txtlong) === '') {
                // leere Zeilen trotzdem als Objekt mit Minimalinfos ausgeben
                $objects[] = [
                    'name'              => null,
                    'bestnr'            => $bnr,
                    'bestpos'           => $pos,
                    'papierklasse'      => null,
                    'papiergrammatur'   => null,
                    'objektformat'      => null,
                    'bogenformat'       => null,
                    'anzahl_nutzen'     => null,
                    'druckbogen_vorderseite' => null,
                    'vorlagen'          => null,
                    'korrekturart'      => null,
                    'versand'           => null,
                    'druck'             => null,
                    'format'            => null,
                    'veredelung'        => null,
                    'offenes_format'    => null,
                    'graupappe'         => null,
                    'mechanik'          => [
                        'typ'       => null,
                        'länge'     => null,
                        'bügelzahl' => null,
                        'füllhöhe'  => null,
                        'position'  => null,
                    ],
                    '_error'            => 'Kein TXTLONG Text vorhanden',
                ];
                continue;
            }

            // "name" aus dem Text ableiten (Innen/Außen), fallback null
            $name = $this->detectObjektName($txtlong);

            // Prompt: ACHTUNG – wir wollen EIN OBJEKT zurück (kein Wrapper), Keys & Reihenfolge wie hier.
            $prompt = <<<PROMPT
Analysiere den folgenden deutschsprachigen Auftrags-Text und gib GENAU EIN JSON-Objekt zurück
(kein Array, keine zusätzlichen Keys). Verwende GENAU die folgenden Felder in dieser Reihenfolge.
"objektformat" ist das Feld für "Mappenformat","Registerformat", oder ähnliche Bezeichnungen mit "Format" außer "Bogenformat". 
Zahlen bitte als Zahl (nicht als String) ausgeben, auch bei "objektformat" als Zahlen ausgeben, also nicht z.B. String "620 mm" sondern 620 als integer.
falls erkennbar (z. B. anzahl_nutzen, bügelzahl).

{
  "name": "#{NAME}",
  "bestnr": "#{BESTNR}",
  "bestpos": "#{BESTPOS}",
  "papierklasse": null,
  "papiergrammatur": null,
  "objektformat": {
    "objektbreite": null,
    "objekthoehe": null,
  },
  "bogenformat": {
    "bogenbreite": null,
    "bogenhoehe": null,
  },
  "anzahl_nutzen": null,
  "druckbogen": null,
  "vorlagen": null,
  "korrekturart": null,
  "versand": null,
  "druck": null,
  "format": null,
  "veredelung": null,
  "offenes_format": null,
  "pappe": {
    "typ": null,
    "staerke": null,
  },
  "mechanik": {
    "typ": null,
    "länge": null,
    "bügelzahl": null,
    "füllhöhe": null,
    "position": null
  }
}

WICHTIG:
- Trage die Platzhalter name/bestnr/bestpos exakt wie vorgegeben ein.
- Mappe "Mappenformat" aus dem Text nach "objektformat".
- Fehlt ein Wert, bleibt er null.
- Gib ausschließlich ein einziges JSON-Objekt zurück, keine Erklärungen.

TEXT:
{$txtlong}
PROMPT;

            $prompt = str_replace(
                ['#{NAME}', '#{BESTNR}', '#{BESTPOS}'],
                [$name ?? '', $bnr, $pos],
                $prompt
            );

            $body = [
                'model' => $model,
                'input' => [
                    ['role' => 'system', 'content' => 'Du bist ein präziser Parser und gibst nur JSON aus.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
                // JSON-Mode der Responses API
                'text' => [
                    'format' => ['type' => 'json_object'],
                ],
                'temperature' => 0,
            ];

            try {
                $resp = $client->request('POST', 'https://api.openai.com/v1/responses', [
                    'body' => json_encode($body, JSON_UNESCAPED_UNICODE),
                ]);
                $data = $resp->toArray(false);

                // bevorzugtes Feld
                $json = $data['output_text'] ?? '';

                // Fallback: output[..].content[..].text
                if ($json === '' && !empty($data['output'])) {
                    foreach ($data['output'] as $item) {
                        if (!empty($item['content']) && is_array($item['content'])) {
                            foreach ($item['content'] as $c) {
                                if (!empty($c['text']) && is_string($c['text'])) {
                                    $json = $c['text'];
                                    break 2;
                                }
                            }
                        }
                    }
                }

                // Code-Fences abstreifen
                $json = preg_replace('/^```(?:json)?|```$/m', '', (string)$json);
                $obj  = json_decode(trim($json), true);

                // Falls AI die drei Meta-Felder nicht gesetzt hat, sicherstellen:
                if (!is_array($obj)) { $obj = []; }
                $obj['name']    = $obj['name']    ?? $name;
                $obj['bestnr']  = $obj['bestnr']  ?? $bnr;
                $obj['bestpos'] = $obj['bestpos'] ?? $pos;

                $objects[] = $obj;
            } catch (\Throwable $e) {
                $objects[] = [
                    'name'              => $name,
                    'bestnr'            => $bnr,
                    'bestpos'           => $pos,
                    'papierklasse'      => null,
                    'papiergrammatur'   => null,
                    'objektformat'      => null,
                    'objektbreite'      => null,
                    'objekthoehe'       => null,
                    'bogenformat'       => null,
                    'bogenbreite'       => null,
                    'bogenhoehe'        => null,
                    'anzahl_nutzen'     => null,
                    'druckbogen_vorderseite' => null,
                    'vorlagen'          => null,
                    'korrekturart'      => null,
                    'versand'           => null,
                    'druck'             => null,
                    'format'            => null,
                    'veredelung'        => null,
                    'offenes_format'    => null,
                    'pappe'         => null,
                    'mechanik'          => [
                        'typ'       => null,
                        'länge'     => null,
                        'bügelzahl' => null,
                        'füllhöhe'  => null,
                        'position'  => null,
                    ],
                    '_error'            => $e->getMessage(),
                ];
            }
        }

        $response = new JsonResponse(['objekt' => $objects]);
        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $response;

    }

    /**
     * Leitet "Bezug außen/innen" aus dem Freitext ab.
     */
    private function detectObjektName(string $txt): ?string
    {
        $t = mb_strtolower($txt, 'UTF-8');

        // Außen / Aussen
        if (preg_match('/\bau(?:ß|ss)en(?:bezüge)?\b/u', $t)) {
            return 'Bezug außen';
        }
        // Innen
        if (preg_match('/\binnen(?:bezüge)?\b/u', $t)) {
            return 'Bezug innen';
        }
        return null;
    }


    /**
     * Zieht robust den Assistant-Text aus verschiedenen Responses-Formaten.
     * Responses API bevorzugt `output_text`, kann aber auch strukturierte `output` liefern.
     */
    private function extractOutputText(array $data): string
    {
        // 1) Neues Feld der Responses-API
        if (!empty($data['output_text']) && is_string($data['output_text'])) {
            return $data['output_text'];
        }
        // 2) Fallback: Items in 'output' durchsuchen
        if (!empty($data['output']) && is_array($data['output'])) {
            foreach ($data['output'] as $item) {
                if (!empty($item['content']) && is_array($item['content'])) {
                    foreach ($item['content'] as $c) {
                        // Manche SDKs nennen es 'output_text', andere schlicht 'text'
                        if (isset($c['text']) && is_string($c['text'])) {
                            return $c['text'];
                        }
                        if (isset($c['output_text']) && is_string($c['output_text'])) {
                            return $c['output_text'];
                        }
                    }
                }
            }
        }
        // 3) Ultimativer Fallback, falls jemand aus Versehen Chat Completions nutzt/proxyed
        if (!empty($data['choices'][0]['message']['content'])) {
            return (string)$data['choices'][0]['message']['content'];
        }
        return '{}';
    }


    #[Route('/api/easy-search', name: 'api_easy_search', methods: ['GET'])]
    public function easySearch(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $em = $doctrine->getManager('easy');
        $conn = $em->getConnection();
        $orderNr = $request->query->get('orderNr', '');

        //  → exakt matchen:
        $sql = <<<SQL
                    SELECT
                        ooa.oxid,
                        oo.oxordernr,
                        ooa.oxtitle,
                        ooa.oxartnum,
                        ooa.oxshortdesc,
                        ooa.ddposition,
                        ooa.oxamount
                    FROM easy_live.oxorderarticles AS ooa
                    JOIN easy_live.oxorder AS oo
                      ON oo.oxid = ooa.oxorderid
                    WHERE oo.oxordernr LIKE :orderNr
                      AND ooa.oxstorno = 0
                    ORDER BY ooa.ddposition
                SQL;

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'orderNr' => '%' . $orderNr,
        ]);

        return $this->json($result->fetchAllAssociative());
    }

    #[Route('/api/ldap-user', name: 'api_ldap_user', methods: ['GET'])]
    public function getCurrentLdapUser(TokenStorageInterface $tokenStorage): JsonResponse
    {
        $token = $tokenStorage->getToken();

        if (!$token || !$token->getUser() instanceof UserInterface) {
            return new JsonResponse([
                'status' => 'unauthenticated',
                'message' => 'Kein authentifizierter Benutzer gefunden.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $username = $token->getUser()->getUserIdentifier();

        try {
            $ldap = Ldap::create('ext_ldap', [
                'host' => $_ENV['LDAP_BASE_ADDRESS'],
                'port' => 389,
                'encryption' => 'none',
                'options' => [
                    'protocol_version' => 3,
                    'referrals' => false,
                ],
            ]);

            $ldap->bind($_ENV['LDAP_SEARCH_DN'], $_ENV['LDAP_SEARCH_PASSWORD']);

            $query = $ldap->query($_ENV['LDAP_BASE_DN'], sprintf('(%s=%s)', $_ENV['LDAP_UID_KEY'] ?? 'sAMAccountName', $username));
            $results = $query->execute();

            if (count($results) === 0) {
                return new JsonResponse(['status' => 'not_found'], JsonResponse::HTTP_NOT_FOUND);
            }

            //dd($token->getUser());

            $entry = $results[0];

            return new JsonResponse([
                'status' => 'ok',
                'username' => $username,
                'email' => $entry->getAttribute('mail')[0] ?? null,
                'firstname' => $entry->getAttribute('givenName')[0] ?? null,
                'lastname' => $entry->getAttribute('sn')[0] ?? null,
                'phone' => $entry->getAttribute('homePhone')[0] ?? null,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/me', name: 'api_me')]
    public function me(TokenStorageInterface $tokenStorage): JsonResponse
    {
        $user = $tokenStorage->getToken()?->getUser();

        if (!$user instanceof UserInterface) {
            return $this->json(['status' => 'unauthenticated'], 401);
        }

        return $this->json([
            'status' => 'ok',
            'username' => $user->getUserIdentifier(),
            'email' => $user->getEmail(),
        ]);
    }

    #[Route('/api/orders', name: 'api_orders', methods: ['GET'])]
    public function listEasyOrders(EntityManagerInterface $em): JsonResponse
    {
        // Hole Orders + Produkte in einem Rutsch
        $qb = $em->createQueryBuilder()
            ->select('o', 'p')
            ->from(EasyOrder::class, 'o')
            ->leftJoin(EasyProduct::class, 'p', 'WITH', 'p.order = o')
            ->orderBy('o.orderNr', 'DESC');

        // als Arrays (statt Entities) zurück
        // Ergebnis ist eine verschachtelte Struktur je nach Hydration.
        // Wir mappen explizit auf ein schlankes, frontend-freundliches JSON:
        $orders = [];

        // Besser: direkt so:
        $ordersRaw = $em->getRepository(EasyOrder::class)->createQueryBuilder('o')
            ->leftJoin('o.products', 'p')
            ->addSelect('p')
            ->orderBy('p.oxOrderNr', 'DESC')
            ->getQuery()
            ->getResult(); // Entities

        foreach ($ordersRaw as $orderEntity) {
            $products = [];
            foreach ($orderEntity->getProducts() ?? [] as $p) {
                $products[] = [
                    'productId' => $p->getProductId(),
                    'productNr' => $p->getProductNr(),
                    'oxOrderNr' => $p->getOxOrderNr(),   // << das Feld aus EasyProduct
                    'ddPosition' => $p->getDdPosition(),
                ];
            }

            $orders[] = [
                'orderid' => $orderEntity->getOrderId(),
                'ordernr' => $orderEntity->getOrderNr(),
                'status' => $orderEntity->getStatus(),
                'products' => $products,
            ];
        }

        return new JsonResponse($orders, 200);
    }

    #[Route('/api/printorders', name: 'api_printorders', methods: ['GET'])]
    public function listEasyPrintOrders(EntityManagerInterface $em): JsonResponse
    {
        // Hole Orders mit ihren Produkten, aber nur Produkte mit listprint = false
        $ordersRaw = $em->getRepository(EasyOrder::class)
            ->createQueryBuilder('o')
            ->leftJoin('o.products', 'p', 'WITH', 'p.listprint = :flag')
            ->addSelect('p')
            ->setParameter('flag', false)
            ->getQuery()
            ->getResult();

        $orders = [];
        foreach ($ordersRaw as $orderEntity) {
            $products = [];
            foreach ($orderEntity->getProducts() ?? [] as $p) {
                // Sicherheitshalber nochmal prüfen
                if ($p->isListprint() === false) {
                    $products[] = [
                        'productId'  => $p->getProductId(),
                        'productNr'  => $p->getProductNr(),
                        'oxOrderNr'  => $p->getOxOrderNr(),
                        'ddPosition' => $p->getDdPosition(),
                        'amount'     => $p->getAmount(),
                        'artnr'      => $p->getArtnr(),
                        'listprint'  => $p->isListprint(),
                    ];
                }
            }

            // Nur Orders zurückgeben, die auch Produkte ohne listprint haben
            if (!empty($products)) {
                $orders[] = [
                    'orderid'  => $orderEntity->getOrderId(),
                    'ordernr'  => $orderEntity->getOrderNr(),
                    'status'   => $orderEntity->getStatus(),
                    'products' => $products,
                ];
            }
        }

        return new JsonResponse($orders, 200);
    }


    #[Route('/api/user', name: 'api_current_user', methods: ['GET'])]
    public function current(?UserInterface $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['status' => 'unauthorized'], 401);
        }

        // Adjust to your LDAP fields if you map them on the User
        return $this->json([
            'status'    => 'ok',
            'username'  => $user->getUserIdentifier(),
            'email'     => method_exists($user, 'getEmail') ? $user->getEmail() : null,
            'firstname' => method_exists($user, 'getFirstname') ? $user->getFirstname() : null,
            'lastname'  => method_exists($user, 'getLastname') ? $user->getLastname() : null,
        ]);
    }

    #[Route('/api/easy-product/exists', name: 'easy_product_exists', methods: ['GET'])]
    public function existsEasy(Request $req, EasyProductRepository $repo): JsonResponse
    {
        // Param-Namen exakt so wie im Frontend:
        $ox = $req->query->get('oxordernr');     // z.B. "305AB2103152"
        $pos = $req->query->getInt('ddposition'); // z.B. 2

        if (!$ox || $pos === 0) {
            return new JsonResponse(['exists' => false, 'error' => 'missing params'], 400);
        }

        $exists = $repo->existsByOxOrderNrAndDdPosition($ox, $pos);
        return new JsonResponse(['exists' => $exists]);
    }

    #[Route('/api/app-product/exists', name: 'app_product_exists', methods: ['GET'])]
    public function existsApp(Request $req, APPProductRepository $repo): JsonResponse
    {
        // Param-Namen exakt so wie im Frontend:
        $ox = $req->query->get('appbestnr');
        $pos = $req->query->getInt('appposnr');

        if (!$ox || $pos === 0) {
            return new JsonResponse(['exists' => false, 'error' => 'missing params'], 400);
        }

        $exists = $repo->existsByAppBestNrAndAppPosNr($ox, $pos);
        return new JsonResponse(['exists' => $exists]);
    }

    #[Route('/api/app-order/by-app', name: 'app_order_by_app', methods: ['GET'])]
    public function getByApp(Request $req, APPOrderRepository $repo): JsonResponse
    {
        $ox  = $req->query->get('appbestnr');
        $pos = $req->query->getInt('appposnr');

        if (!$ox || $pos === 0) {
            return new JsonResponse(['exists' => false, 'error' => 'missing params'], 400);
        }

        $order = $repo->findOneBy([
            'appbestnr' => $ox,
            'appposnr'  => $pos,
        ]);

        if (!$order) {
            return new JsonResponse(['exists' => false]);
        }

        return new JsonResponse([
            'exists'  => true,
            'orderid' => $order->getOrderId(),
            'ordernr' => $order->getOrderNr(),
        ]);
    }


    #[Route('/api/orders/orderid', name: 'api_orders_orderid', methods: ['GET'])]
    public function findOrderId(
        Request               $request,
        EasyProductRepository $easyProductRepo
    ): JsonResponse
    {
        $oxordernr = $request->query->get('oxordernr');
        $ddposition = $request->query->getInt('ddposition', 0);

        if (!$oxordernr || !$ddposition) {
            return $this->json(['success' => 0, 'error' => 'oxordernr und ddposition erforderlich'], 400);
        }

        // 1) Über Entity/Repository suchen
        $orderId = $easyProductRepo->existsByOxOrderNrAndDdPosition($oxordernr, $ddposition);


        if ($orderId) {
            return $this->json(['success' => 1, 'orderid' => $orderId]);
        }

        return $this->json(['success' => 0, 'error' => 'orderid nicht gefunden'], 404);
    }

    /**
    * Behandelt HTTP-GET-Anfragen, um eine Liste von Anwendungsbestellungen zusammen
    * mit den zugehörigen Produkten abzurufen.
    * Die Methode holt Bestellungen als Entitäten und bildet sie in eine verschachtelte
    * JSON-Struktur ab. Jede Bestellung enthält relevante Metadaten sowie die zugehörigen
    * Produkte.
    * @param EntityManagerInterface $em Der Doctrine-Entity-Manager, der für die
    * Datenbankinteraktion verwendet wird.
    * @return JsonResponse Gibt eine JSON-Antwort zurück, die ein Array von Bestellungen enthält,
    * wobei jede ihre Metadaten und zugehörigen Produkte hat.
     */
    #[Route('/api/app-orders', name: 'api_app_orders', methods: ['GET'])]
    public function listAppOrders(EntityManagerInterface $em): JsonResponse
    {
        // Hole Orders + Produkte in einem Rutsch
        // als Arrays (statt Entities) zurück
        // Ergebnis ist eine verschachtelte Struktur je nach Hydration.
        // Wir mappen explizit auf ein schlankes, frontend-freundliches JSON:
        $orders = [];

        // Besser: direkt so:
        $ordersRaw = $em->getRepository(APPOrder::class)->createQueryBuilder('o')
            ->leftJoin('o.products', 'p')
            ->addSelect('p')
            ->getQuery()
            ->getResult(); // Entities

        foreach ($ordersRaw as $orderEntity) {
            $products = [];
            foreach ($orderEntity->getProducts() ?? [] as $p) {
                $products[] = [
                    'productId' => $p->getProductId(),
                    'productNr' => $p->getProductNr(),
                    'oxOrderNr' => $p->getAppBestNr(),   // << das Feld aus EasyProduct
                    'ddPosition' => $p->getBestPosition(),
                ];
            }

            $orders[] = [
                'orderid'   => $orderEntity->getOrderId(),
                'ordernr'   => $orderEntity->getOrderNr(),
                'appbestnr' => $orderEntity->getAppBestNr(),
                'appposnr'  => $orderEntity->getAppposnr(),
                'status'    => $orderEntity->getStatus(),
                'products'  => $products,
            ];
        }

        return new JsonResponse($orders, 200);
    }
}
