<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Main\EasyProduct;
use App\Entity\Main\PrintList;
use App\Entity\Main\PrintListItem;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PrintListController extends AbstractController
{
    #[Route('/api/printlist/create', name: 'api_printlist_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Payload: { productIds: [1,2,3] } oder leer => alle EasyProduct mit listprint=false
        $data = json_decode($request->getContent() ?? '[]', true) ?? [];
        $ids  = array_filter(array_map('intval', $data['productIds'] ?? []));

        $qb = $em->getRepository(EasyProduct::class)->createQueryBuilder('p')
            ->andWhere('p.listprint = :flag')->setParameter('flag', false);

        if (!empty($ids)) {
            $qb->andWhere('p.id IN (:ids)')->setParameter('ids', $ids);
        }

        /** @var EasyProduct[] $products */
        $products = $qb->getQuery()->getResult();

        if (!$products) {
            return new JsonResponse(['error' => 'Keine passenden Produkte gefunden (listprint=false).'], 400);
        }

        $em->beginTransaction();
        try {
            $list = new PrintList();
            $em->persist($list);
            $em->flush(); // ID erzeugt

            $list->setNumber(sprintf('%05d', $list->getId()));
            $em->flush();

            foreach ($products as $prod) {
                // Mapping erstellen
                $li = (new PrintListItem())
                    ->setPrintList($list)
                    ->setEasyProduct($prod);
                $em->persist($li);

                // Flag setzen
                $prod->setListprint(true);
            }

            $em->flush();
            $em->commit();

            return new JsonResponse([
                'success' => true,
                'id'      => $list->getId(),
                'count'   => count($products),
            ]);
        } catch (\Throwable $e) {
            $em->rollback();
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/printlist/{id}/pdf', name: 'api_printlist_pdf', methods: ['GET'])]
    public function pdf(
        Request $request,
        int $id,
        EntityManagerInterface $em,
        #[Autowire('%kernel.project_dir%')] string $projectDir
    ): Response
    {
        /** @var PrintList|null $list */
        $list = $em->getRepository(PrintList::class)->find($id);
        if (!$list) {
            return new Response('Liste nicht gefunden', 404);
        }

        // Hole alle zugeordneten Produkte (JOIN über PrintListItem)
        $items = $em->getRepository(PrintListItem::class)->createQueryBuilder('li')
            ->join('li.easyProduct', 'p')->addSelect('p')
            ->andWhere('li.printList = :list')->setParameter('list', $list)
            ->getQuery()->getResult();

        // Baue Zeilen
        $rows = '';
        foreach ($items as $li) {
            /** @var EasyProduct $p */
            $p = $li->getEasyProduct();

            $oxordernr = (string)$p->getOxOrderNr();
            $amount    = (int)$p->getAmount();
            $artnr     = (string)($p->getArtnr() ?? '');
            $prnr      = (string)($p->getProductNr() ?? '');
            $prefix3   = strtoupper(substr($artnr, 0, 3));

            // Folie aus ARTNR (enthält VEA10/20/30)
            $folie = '—';
            $upper = strtoupper($artnr);
            if (str_contains($upper, 'VEA10'))      $folie = 'Glanz';
            elseif (str_contains($upper, 'VEA20')) $folie = 'Matt';
            elseif (str_contains($upper, 'VEA30')) $folie = 'X-Treme';

            // Bogenformat fix
            $bogen = '700 × 500 mm';

            // Grammatur: 135g, außer wenn REG am Anfang -> 300g
            $grammatur = ($prefix3 === 'REG') ? '300 g' : '135 g';

            $rows .= sprintf(
                '<tr>
                    <td>%s</td>
                    <td>%s</td>
                    <td class="num">%d</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                 </tr>',
                htmlspecialchars($oxordernr),
                htmlspecialchars($prnr),
                $amount,
                htmlspecialchars($folie),
                htmlspecialchars($prefix3),
                $bogen,
                $grammatur
            );
        }

        $created = $list->getCreatedAt()->format('d.m.Y');
        $html = <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Druckliste</title>
<style>
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
  h1 { font-size: 18px; margin: 0 0 8px 0; }
  .meta { font-size: 11px; margin-bottom: 12px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #999; padding: 6px 8px; vertical-align: top; }
  th { background: #f3f3f3; text-align: left; }
  .num { text-align: right; }
</style>
</head>
<body>
  <h1>Druckliste easyOrdner {$created} - {$list->getNumber()}</h1>
  <div class="meta">Erstellt am: {$created}</div>
  <table>
    <thead>
      <tr>
        <th>Auftragsnummer</th>
        <th>Pinguin-PR-Nr.</th>
        <th>Menge</th>
        <th>Folie</th>
        <th>Artikel</th>
        <th>Bogenformat</th>
        <th>Grammatur</th>
      </tr>
    </thead>
    <tbody>
      {$rows}
    </tbody>
  </table>
</body>
</html>
HTML;

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdf = $dompdf->output();

        // --- PDF auf Server speichern ---
        $saveDir  = $projectDir . '/var/printlists';
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0777, true);
        }

        $filename = sprintf('printlist-%d.pdf', $list->getId());
        $savePath = $saveDir . '/' . $filename;
        file_put_contents($savePath, $pdf);

        $disposition = ($request->query->getBoolean('download'))
            ? 'attachment'
            : 'inline';

        return new Response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="'.$filename.'"',
        ]);
    }

    #[Route('/api/printlists', name: 'api_printlists', methods: ['GET'])]
    public function listAll(EntityManagerInterface $em): JsonResponse
    {
        /** @var PrintList[] $lists */
        $lists = $em->getRepository(PrintList::class)
            ->createQueryBuilder('pl')
            ->orderBy('pl.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $out = [];
        foreach ($lists as $pl) {
            $count = (int)$em->getRepository(PrintListItem::class)
                ->createQueryBuilder('li')
                ->select('COUNT(li.id)')
                ->andWhere('li.printList = :pl')
                ->setParameter('pl', $pl)
                ->getQuery()
                ->getSingleScalarResult();

            $out[] = [
                'id'         => $pl->getId(),
                'number' => $pl->getNumber() ?? sprintf('%05d', $pl->getId()),
                'createdAt'  => $pl->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'itemsCount' => $count,
                // optional: gleich den PDF-Link mitsenden:
                'pdfUrl'     => sprintf('/api/printlist/%d/pdf', $pl->getId()),
            ];
        }

        return new JsonResponse($out, 200);
    }
}
