<?php

namespace App\Controller;

use App\Entity\Main\Bestellungen;
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

}
