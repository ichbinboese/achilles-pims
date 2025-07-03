<?php

namespace App\Controller;

use App\Entity\Main\PimsKaschierung;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Main\PimsPapier;
use App\Entity\Main\PimsProdukt;
use App\Entity\Main\PimsDruckfarben;

class PimsOrderController extends AbstractController
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }


    #[Route('/api/proxy/pims-order', name: 'proxy_pims_order', methods: ['POST'])]
    public function proxyPimsOrder(Request $request): JsonResponse
    {
        try {
            $formData = $request->files->all() + $request->request->all();

            $response = $this->client->request('POST', 'https://pims-api.stage.printdays.net/v1/pimsOrder.php', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $_ENV['PIMS_API_AUTH']
                ],
                'body' => $formData,
            ]);

            $data = $response->toArray();

            return new JsonResponse($data, $response->getStatusCode());
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Proxy-Fehler bei Bestellung: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/proxy/pims-product', name: 'pims_proxy_product', methods: ['POST'])]
    public function proxyProduct(Request $request): Response
    {
        $client = HttpClient::create();

        $authHeader = $_ENV['PIMS_API_AUTH'];
        $apiBase = $_ENV['PIMS_API_BASE'];

        $response = $client->request('POST', $apiBase . '/pimsProduct.php', [
            'headers' => [
                'Authorization' => $authHeader,
                'Accept' => 'application/json',
            ],
            'body' => $request->request->all(),
            'extra' => [
                'files' => $request->files->all()
            ]
        ]);

        $data = $response->getContent(false);
        return new Response($data, $response->getStatusCode(), [
            'Content-Type' => $response->getHeaders(false)['content-type'][0] ?? 'application/json'
        ]);
    }

    #[Route('/api/proxy/pims-parcel', name: 'proxy_pims_parcel', methods: ['POST'])]
    public function proxyPimsParcel(Request $request): JsonResponse
    {
        try {
            $formData = $request->files->all() + $request->request->all();

            $response = $this->client->request('POST', 'https://pims-api.stage.printdays.net/v1/pimsParcel.php', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $_ENV['PIMS_API_AUTH'],
                ],
                'body' => $formData,
            ]);

            return new JsonResponse($response->toArray(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Proxy-Fehler bei Versandanfrage: ' . $e->getMessage()
            ], 500);
        }
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
}
