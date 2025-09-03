<?php

namespace App\Repository;

use App\Entity\Main\EasyProductMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EasyProductMapping>
 */
class EasyProductMappingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EasyProductMapping::class);
    }

    /**
     * Findet das beste (längste) Snippet-Match für die gegebene oxartnum.
     */
    public function findBestMatch(string $oxartnum): ?EasyProductMapping
    {
        $qb = $this->createQueryBuilder('e');

        // :ox LIKE CONCAT('%', e.artnumsnippet, '%')
        $qb->where(
            $qb->expr()->like(
                ':ox',
                $qb->expr()->concat(
                    $qb->expr()->literal('%'),
                    'e.artnumsnippet',
                    $qb->expr()->literal('%')
                )
            )
        )
            ->setParameter('ox', $oxartnum)
            // Längstes Snippet zuerst
            ->addOrderBy('LENGTH(e.artnumsnippet)', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
