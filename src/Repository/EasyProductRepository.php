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

    public function existsByOxOrderNrAndDdPosition(string $oxOrderNr, int $ddPosition): bool
    {
        return (bool) $this->createQueryBuilder('e')
            ->select('1')
            ->andWhere('e.oxOrderNr = :ox')
            ->andWhere('e.ddPosition = :pos')
            ->setParameter('ox', trim($oxOrderNr))
            ->setParameter('pos', $ddPosition)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
