<?php
namespace App\Repository;

use App\Entity\Main\APPProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class APPProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, APPProduct::class);
    }

    public function existsByAppBestNrAndAppPosNr(string $appbestnr, int $appposnr): ?string
    {
        $result = $this->createQueryBuilder('e')
            ->select('eo.orderId')  // Verweis auf das orderId-Feld in EasyOrder
            ->leftJoin('e.order', 'eo')  // Join mit der EasyOrder-Entität
            ->andWhere('eo.appbestnr = :ox')
            ->andWhere('eo.appposnr = :pos')
            ->setParameter('ox', trim($appbestnr))
            ->setParameter('pos', $appposnr)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result && isset($result['orderId'])) {
            return $result['orderId'];  // Gebe die orderId zurück
        }

        return null;  // Falls keine orderId gefunden wurde
    }
}
