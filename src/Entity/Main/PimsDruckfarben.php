<?php

namespace App\Entity\Main;

use App\Repository\DruckfarbenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DruckfarbenRepository::class)]
class PimsDruckfarben
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 10)]
    private string $code;

    #[ORM\Column(type: 'string', length: 255)]
    private string $bezeichnung;

    #[ORM\Column(type: 'string', length: 255)]
    private string $achillesmapping;

    #[ORM\Column(type: 'string', length: 255)]
    private string $easymapping;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function __toString(): string
    {
        return $this->bezeichnung;
    }

    public function getAchillesmapping(): ?string
    {
        return $this->achillesmapping;
    }

    public function setAchillesmapping(string $achillesmapping): static
    {
        $this->achillesmapping = $achillesmapping;
        return $this;
    }

    public function getBezeichnung(): ?string
    {
        return $this->bezeichnung;
    }

    public function setBezeichnung(string $bezeichnung): static
    {
        $this->bezeichnung = $bezeichnung;
        return $this;
    }

    public function getEasymapping(): ?string
    {
        return $this->easymapping;
    }

    public function setEasymapping(string $easymapping): static
    {
        $this->easymapping = $easymapping;
        return $this;
    }
}
