<?php
namespace App\Repository;

use App\Entity\Main\EasyProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EasyProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EasyProduct::class);
    }

    public function existsByOxOrderNrAndDdPosition(string $oxOrderNr, int $ddPosition): ?string
    {
        $result = $this->createQueryBuilder('e')
            ->select('eo.orderId')  // Verweis auf das orderId-Feld in EasyOrder
            ->leftJoin('e.order', 'eo')  // Join mit der EasyOrder-Entität
            ->andWhere('e.oxOrderNr = :ox')
            ->andWhere('e.ddPosition = :pos')
            ->setParameter('ox', trim($oxOrderNr))
            ->setParameter('pos', $ddPosition)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result && isset($result['orderId'])) {
            return $result['orderId'];  // Gebe die orderId zurück
        }

        return null;  // Falls keine orderId gefunden wurde
    }
}
