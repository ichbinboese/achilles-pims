<?php
declare(strict_types=1);

namespace App\Entity\Main;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Column]
    private string $password;

    public function getPassword(): string { return $this->password; }

    public function getUserIdentifier(): string { return $this->email; }
}