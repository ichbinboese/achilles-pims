<?php

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[UniqueEntity(
    fields: ['appfirma', 'appbestellnummer', 'appbestellposition'],
    message: 'Diese Kombination aus Firma, Bestellnummer und Position existiert bereits.'
)]
#[ORM\Table(name: 'bestellungen')]
#[ORM\UniqueConstraint(
    name: 'unique_bestellung',
    columns: ['appfirma', 'appbestellnummer', 'appbestellposition']
)]
class Bestellungen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $appbestellnummer;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $appfirma;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'integer')]
    private int $appbestellposition;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $pimsid;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $pimsbestellnummer;

    public function getId(): ?int { return $this->id; }

    public function getAppbestellnummer(): string { return $this->appbestellnummer; }
    public function setAppbestellnummer(string $value): self { $this->appbestellnummer = $value; return $this; }

    public function getAppbestellposition(): int { return $this->appbestellposition; }
    public function setAppbestellposition(int $value): self { $this->appbestellposition = $value; return $this; }

    public function getPimsid(): string { return $this->pimsid; }
    public function setPimsid(string $value): self { $this->pimsid = $value; return $this; }

    public function getPimsbestellnummer(): string { return $this->pimsbestellnummer; }
    public function setPimsbestellnummer(string $value): self { $this->pimsbestellnummer = $value; return $this; }

    public function getAppfirma(): string { return $this->appfirma; }
    public function setAppfirma(string $value): self { $this->appfirma = $value; return $this; }
}
