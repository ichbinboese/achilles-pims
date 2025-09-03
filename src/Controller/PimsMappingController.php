<?php
namespace App\Controller;

use App\Service\PimsMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;
use App\Repository\EasyProductMappingRepository;

#[AsController] // <<< füge das hinzu
final class PimsMappingController
{
    #[Route('/api/pims-map', name: 'api_pims_map', methods: ['POST'])]
    public function map(Request $req, PimsMapper $mapper): JsonResponse
    {
        $payload = json_decode($req->getContent(), true) ?? [];
        $oxartnum = (string)($payload['oxartnum'] ?? '');
        if ($oxartnum === '') {
            return new JsonResponse(['error' => 'oxartnum missing'], 400);
        }

        $codes = $mapper->resolveCodesForItem($oxartnum);
        return new JsonResponse(['codes' => $codes]);
    }

    #[Route('/api/easy-size', name: 'api_easy_size', methods: ['GET'])]
    public function __invoke(Request $request, EasyProductMappingRepository $repo): JsonResponse
    {
        $oxartnum = (string) $request->query->get('oxartnum', '');

        if ($oxartnum === '') {
            return new JsonResponse(['error' => 'oxartnum required'], 400);
        }

        $match = $repo->findBestMatch($oxartnum);

        if (!$match) {
            return new JsonResponse(['match' => null], 200);
        }

        return new JsonResponse([
            'match' => [
                'snippet' => $match->getArtnumsnippet(),
                'name'    => $match->getName(),
                'width'   => $match->getWidth(),
                'height'  => $match->getHeight(),
            ],
        ]);
    }
}
