<?php
declare(strict_types=1);

namespace App\Repository\Oracle;

use App\Entity\Oracle\E110;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<E110>
 */
class E110Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, E110::class);
    }

    /**
     * Gibt alle Positionen einer Bestellung inkl. zugehörigem Text (B4000) zurück.
     */
    public function findBestellungMitText(int $fiNr, string $bestnr): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.text', 'b')
            ->addSelect('b')
            ->where('e.fiNr = :fiNr')
            ->andWhere('e.bestnr = :bestnr')
            ->andWhere('b.txtArt = :txtArt')
            ->setParameter('fiNr', $fiNr)
            ->setParameter('bestnr', $bestnr)
            ->setParameter('txtArt', 'TQP')
            ->orderBy('e.bestpos', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
