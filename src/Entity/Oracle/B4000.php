<?php
declare(strict_types=1);

namespace App\Entity\Oracle;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'B4000')]
class B4000
{
    #[ORM\Id]
    #[ORM\Column(name: 'FI_NR', type: 'integer')]
    private int $fiNr;

    #[ORM\Id]
    #[ORM\Column(name: 'SATZART', type: 'string')]
    private string $satzart;

    #[ORM\Id]
    #[ORM\Column(name: 'KONTO', type: 'string')]
    private string $konto;

    #[ORM\Column(name: 'TXT_ART', type: 'string')]
    private string $txtArt;

    #[ORM\Column(name: 'TXT_NR', type: 'integer')]
    private int $txtNr;

    // --- Getter & Setter ---

    public function getFiNr(): int
    {
        return $this->fiNr;
    }

    public function setFiNr(int $fiNr): self
    {
        $this->fiNr = $fiNr;
        return $this;
    }

    public function getSatzart(): string
    {
        return $this->satzart;
    }

    public function setSatzart(string $satzart): self
    {
        $this->satzart = $satzart;
        return $this;
    }

    public function getKonto(): string
    {
        return $this->konto;
    }

    public function setKonto(string $konto): self
    {
        $this->konto = $konto;
        return $this;
    }

    public function getTxtArt(): string
    {
        return $this->txtArt;
    }

    public function setTxtArt(string $txtArt): self
    {
        $this->txtArt = $txtArt;
        return $this;
    }

    public function getTxtNr(): int
    {
        return $this->txtNr;
    }

    public function setTxtNr(int $txtNr): self
    {
        $this->txtNr = $txtNr;
        return $this;
    }
}
