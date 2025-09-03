<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Main\EasyOrder;
use App\Entity\Main\EasyProduct;
use Doctrine\ORM\EntityManagerInterface;

class PimsDataService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Methode zum Speichern von Bestell- und Produktdaten
    public function storeOrderAndProductData(array $orderResponse, array $productResponse): void
    {
        // Erstellen und setzen der Bestell-Entität
        $order = new EasyOrder();
        $order->setOrderId($orderResponse['orderid']);
        $order->setOrderNr($orderResponse['ordernr']);
        $order->setStatus($orderResponse['status']);

        // Erstellen und setzen der Produkt-Entität
        $product = new EasyProduct();
        $product->setProductId($productResponse['productid']);
        $product->setProductNr($productResponse['productnr']);
        $product->setOxOrderNr($orderResponse['oxordernr']);
        $product->setDdPosition($orderResponse['ddposition']);
        $product->setOrder($order);

        // Persistieren der Entitäten in der Datenbank
        $this->entityManager->persist($order);
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }
}
