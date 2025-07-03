<?php

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PimsFormat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 10)]
    private string $code;

    #[ORM\Column(type: 'string', length: 255)]
    private string $bezeichnung;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $achillesmapping = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getBezeichnung(): string
    {
        return $this->bezeichnung;
    }

    public function setBezeichnung(string $bezeichnung): self
    {
        $this->bezeichnung = $bezeichnung;
        return $this;
    }

    public function getAchillesmapping(): ?string
    {
        return $this->achillesmapping;
    }

    public function setAchillesmapping(?string $achillesmapping): self
    {
        $this->achillesmapping = $achillesmapping;
        return $this;
    }
}
