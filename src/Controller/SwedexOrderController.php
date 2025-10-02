<?php
namespace App\Controller;

use App\Repository\EasyProductMappingRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class SwedexOrderController extends AbstractController
{
    #[Route('/api/swedex/order/send', name: 'api_swedex_order_send', methods: ['POST'])]
    public function send(
        Request $request,
        MailerInterface $mailer,
        Environment $twig,
        EasyProductMappingRepository $epmRepo
    ): Response {
        // --- Eingaben
        $payload = json_decode($request->request->get('order', '{}'), true) ?? [];

        $filefront = $request->files->get('filefront')
            ?: $request->files->get('fileFront')
                ?: $request->files->get('file_front');

        $fileback  = $request->files->get('fileback')
            ?: $request->files->get('fileBack')
                ?: $request->files->get('file_back');

        // Pflicht: filefront

        if (!$filefront instanceof UploadedFile || !$filefront->isValid()) {
            return $this->json([
                'ok' => false,
                'error' => 'filefront_required',
                'message' => 'Bitte laden Sie die Frontdatei (filefront) hoch.'
            ], 400);
        }

        // --- Mapping laut Vorgabe
        $oxordnernr = trim((string)($payload['oxordnernr'] ?? ''));
        $oxtitle    = trim((string)($payload['oxtitle']    ?? ''));
        $oxamount   = (int)($payload['oxamount'] ?? 0);
        $artnum     = trim((string)($payload['artnum']     ?? ''));
        $preis      = (float)($payload['preis']            ?? 0);
        $oxshortdesc = trim((string)($payload['oxshortdesc'] ?? ''));

        // Datum = heute + 10 Arbeitstage
        $now = new \DateTimeImmutable('today');
        $datumPlus10AT = $this->addBusinessDays($now, 10);

        $formatFromDb = null;
        $halterFromDb = null;
        $pappenFromDb = null;

        if ($artnum !== '') { // $artnum = volle oxartnum aus Payload
            $map = $epmRepo->findBestByOxartnum($artnum);
            if ($map) {
                $formatFromDb  = $map->getFormat();
                $halterFromDb  = $map->getHalter();
                $pappenFromDb  = $map->getPappenstaerke();
            }
        }

        $posFormat = $formatFromDb ?: ($payload['pos_format'] ?? null);

        // Summe = oxamount * preis
        $summe = $oxamount * $preis;

        $veredelung = $this->decodeVeredelungFromArtnr($artnum);
        $druck      = $this->decodeDruckFromArtnr($artnum);

        // --- Normiertes Order-Objekt für Twig
        $order = [
            'bestnr' => $oxordnernr,
            'datum'  => $datumPlus10AT,
            'summe'  => $summe,
            'produkt' => [
                'bezeichnung'    => $oxtitle,
                'menge'          => $oxamount,
                'druck'          => $druck,
                'preis'          => $preis,
                'format'         => $posFormat,
                'halter'         => $halterFromDb,      // << neu
                'pappenstaerke'  => $pappenFromDb,      // << neu
                'veredelung'     => $veredelung,
                'shortdesc'      => $oxshortdesc,
            ],
            'positionen' => [[
                'termin'       => $datumPlus10AT,
                'pos'          => 10,
                'beschreibung' => $oxtitle,
                'format'       => $posFormat,
                'menge'        => $oxamount,
                'me'           => 'STK',
                'preis'        => $preis,
            ]],
        ];


        // --- PDF rendern
        $pdfHtml   = $twig->render('swedex/order_swedex.pdf.twig', ['order' => $order, 'now' => $now]);
        $pdfBinary = $this->renderPdf($pdfHtml);

        // --- E-Mail
        $email = (new TemplatedEmail())
            ->from(new Address('info@easyordner.de', 'easyOrdner.de'))
            ->replyTo(new Address('info@easyordner.de', 'easyOrdner.de'))  // Antworten gehen hierhin
            ->to(
                new Address('bbe@achilles.de', 'Benjamin Boese'),
                new Address('info@easyordner.de', 'EasyOrdner'),
                new Address('info@albanyomda.hu', 'Alba Nyomda')
            )
            ->cc(
                new Address('order@impressic.hu', 'Impressic'),
                new Address('bernd.carl@impressic.hu', 'Bernd Carl')
            )
//            ->to(
//                new Address('bbe@achilles.de', 'Benjamin Boese'),
//            )
            ->subject(sprintf('Neue Bestellung %s', $order['bestnr']))
            ->htmlTemplate('swedex/order_email.html.twig')
            ->context(['order' => $order])
            ->attach($pdfBinary, $this->buildPdfName($order), 'application/pdf');


        $email->attach(
            file_get_contents($filefront->getPathname()),
            $filefront->getClientOriginalName(),
            $filefront->getMimeType() ?: 'application/pdf'
        );

        if ($fileback instanceof UploadedFile && $fileback->isValid()) {
            $email->attach(
                file_get_contents($fileback->getPathname()),
                $fileback->getClientOriginalName(),
                $fileback->getMimeType() ?: 'application/pdf'
            );
        }

        $mailer->send($email);

        return $this->json(['ok' => true]);
    }

    private function renderPdf(string $html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }

    private function buildPdfName(array $order): string
    {
        $bn = $order['bestnr'] ?? 'bestellung';
        return sprintf('Bestellung_%s.pdf', preg_replace('/\W+/', '_', (string)$bn));
    }

    /** +N Arbeitstage (Mo–Fr), ohne Feiertage */
    private function addBusinessDays(\DateTimeImmutable $start, int $days): \DateTimeImmutable
    {
        $date = $start;
        $added = 0;
        while ($added < $days) {
            $date = $date->modify('+1 day');
            $w = (int)$date->format('N'); // 1=Mo ... 7=So
            if ($w <= 5) {
                $added++;
            }
        }
        return $date;
    }

    private function decodeVeredelungFromArtnr(?string $artnr): ?string
    {
        if (!$artnr) return null;
        $u = strtoupper($artnr);

        // Reihenfolge bewusst gewählt – einfach erweiterbar
        $map = [
            'VEA11' => 'Leinenstruktur Glanz',
            'VEA10' => 'Glanz',
            'VEA21' => 'Leinenstruktur Matt',
            'VEA20' => 'Matt',
            'VEA30' => 'Matt kratzfest',
            'VEA40' => 'Softtouch Folie',
        ];
        foreach ($map as $code => $label) {
            if (str_contains($u, $code)) return $label;
        }
        return null;
    }

    private function decodeDruckFromArtnr(?string $artnr): ?string
    {
        if (!$artnr) return null;
        $u = strtoupper($artnr);

        // Reihenfolge so, dass „/4“ vor „/0“ ausgewertet wird
        $map = [
            'DRU11' => '4/4-farbig Euroskala Offsetdruck',
            'DRU10' => '4/0-farbig Euroskala Offsetdruck',
            'DRU21' => '4/4-farbig Euroskala Digitaldruck',
            'DRU20' => '4/0-farbig Euroskala Digitaldruck',
        ];
        foreach ($map as $code => $label) {
            if (str_contains($u, $code)) return $label;
        }
        return null;
    }
}
