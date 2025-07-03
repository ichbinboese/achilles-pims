<?php

namespace App\Controller;

use App\Entity\Main\Bestellungen;
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

class IndexController extends AbstractController
{
    private EntityManagerInterface $oracleManager;

    public function __construct(ManagerRegistry $registry) {
        $this->oracleManager = $registry->getManager('oracle');
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('base.html.twig');
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
        SELECT *
        FROM E110 a
        LEFT JOIN B4000 b 
            ON b.FI_NR = a.FI_NR 
           AND b.TXT_NR = a.TXT_NR 
           AND b.TXT_ART = 'TQP'
        WHERE a.FI_NR = :fiNr AND a.BESTNR = :bestnr
        ORDER BY a.BESTPOS
    SQL;

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'fiNr' => $fiNr,
            'bestnr' => $bestnr
        ]);

        return array_map(function ($row) {
            return [
                'fiNr' => $row['FI_NR'],
                'bestnr' => $row['BESTNR'],
                'bestpos' => $row['BESTPOS'],
                'txtlong' => $row['TXTLONG'] ?? null,
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
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $bestellung = new Bestellungen();
        $bestellung->setAppbestellnummer($data['appbestellnummer'] ?? '');
        $bestellung->setAppbestellposition((int) ($data['appbestellposition'] ?? 0));
        $bestellung->setPimsid($data['pimsid'] ?? '');
        $bestellung->setPimsbestellnummer($data['pimsbestellnummer'] ?? '');
        $bestellung->setAppfirma($data['appfirma'] ?? '');

        // ✅ Validierung starten
        $errors = $validator->validate($bestellung);

        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return $this->json(['error' => 'Validierung fehlgeschlagen', 'details' => $messages], 400);
        }

        // ✅ Speichern
        $em->persist($bestellung);
        $em->flush();

        return $this->json(['status' => 'created', 'id' => $bestellung->getId()]);
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

            $entry = $results[0];

            return new JsonResponse([
                'status' => 'ok',
                'username' => $username,
                'email' => $entry->getAttribute('mail')[0] ?? null,
                'firstname' => $entry->getAttribute('givenName')[0] ?? null,
                'lastname' => $entry->getAttribute('sn')[0] ?? null,
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
        ]);
    }

}
