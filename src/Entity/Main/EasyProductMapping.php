<?php

namespace App\Entity\Main;

use App\Repository\EasyProductMappingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EasyProductMappingRepository::class)]
class EasyProductMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $artnumsnippet = null;

    #[ORM\Column]
    private ?int $width = null;

    #[ORM\Column]
    private ?int $height = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $format = null;

    #[ORM\Column(length: 255)]
    private ?string $halter = null;

    #[ORM\Column(length: 255)]
    private ?string $pappenstaerke = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArtnumsnippet(): ?string
    {
        return $this->artnumsnippet;
    }

    public function setArtnumsnippet(string $artnumsnippet): static
    {
        $this->artnumsnippet = $artnumsnippet;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function getHalter(): ?string
    {
        return $this->halter;
    }

    public function setHalter(string $halter): static
    {
        $this->halter = $halter;
        return $this;
    }

    public function getPappenstaerke(): ?string
    {
        return $this->pappenstaerke;
    }

    public function setPappenstaerke(string $pappenstaerke): static
    {
        $this->pappenstaerke = $pappenstaerke;
        return $this;
    }
}
