<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Main\AuthToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuthToken>
 */
class AuthTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthToken::class);
    }

    // Hier kannst du eigene Methoden ergänzen, z.B. findValidToken()
}
