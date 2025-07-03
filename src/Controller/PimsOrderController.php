<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PimsOrderController extends AbstractController
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/api/pims-order', name: 'pims_order_create', methods: ['POST'])]
    public function createOrder(Request $request): JsonResponse
    {
        $formData = $request->request->all();

        $formFields = [
            'uniqueid', 'title', 'name', 'additional1', 'additional2', 'street', 'postcode',
            'locale', 'country', 'mail', 'vat', 'payment', 'number'
        ];

        $multipart = [];
        foreach ($formFields as $field) {
            if (isset($formData[$field])) {
                $multipart[] = ['name' => $field, 'contents' => $formData[$field]];
            }
        }

        $response = $this->client->request('POST', 'https://pims-api.stage.printdays.net/v1/pimsOrder.php', [
            'headers' => [
                'Authorization' => 'Basic QmVuamFtaW4uQm9lc2U6LHhLUTFOei4lRFpZTTc/Qw==',
            ],
            'body' => $multipart,
        ]);

        $data = $response->toArray(false);

        return $this->json($data);
    }
}
