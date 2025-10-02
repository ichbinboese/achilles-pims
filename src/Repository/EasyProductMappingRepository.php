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

        // Case-insensitive Contains:
        // LOWER(:ox) LIKE CONCAT('%', LOWER(e.artnumsnippet), '%')
        $qb->andWhere(
            $qb->expr()->like(
                'LOWER(:ox)',
                $qb->expr()->concat(
                    $qb->expr()->literal('%'),
                    'LOWER(e.artnumsnippet)',
                    $qb->expr()->literal('%')
                )
            )
        )
            ->andWhere('e.artnumsnippet IS NOT NULL')
            // ->andWhere("e.artnumsnippet <> ''") // optional, falls Leerstrings möglich sind
            ->setParameter('ox', mb_strtolower($oxartnum))
            // Länge als HIDDEN selektieren und darauf sortieren (konsistent zu Methode 2)
            ->addSelect('LENGTH(e.artnumsnippet) AS HIDDEN sniplen')
            ->orderBy('sniplen', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findBestByOxartnum(string $oxartnum): ?EasyProductMapping
    {
        $qb = $this->createQueryBuilder('m');

        // Case-insensitive Prefix:
        // LOWER(:oxartnum) LIKE CONCAT(LOWER(m.artnumsnippet), '%')
        $exprLike = $qb->expr()->like(
            'LOWER(:oxartnum)',
            $qb->expr()->concat('LOWER(m.artnumsnippet)', $qb->expr()->literal('%'))
        );

        return $qb
            ->andWhere($exprLike)
            ->andWhere('m.artnumsnippet IS NOT NULL')
            // ->andWhere("m.artnumsnippet <> ''") // optional
            ->setParameter('oxartnum', mb_strtolower($oxartnum))
            ->addSelect('LENGTH(m.artnumsnippet) AS HIDDEN sniplen')
            ->orderBy('sniplen', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
