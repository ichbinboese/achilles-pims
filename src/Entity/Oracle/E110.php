<?php
declare(strict_types=1);


namespace App\Entity\Oracle;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'E110')]
class E110
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

    #[ORM\Column(name: 'BESTNR', type: 'string')]
    private string $bestnr;

    #[ORM\Column(name: 'BESTPOS', type: 'integer')]
    private int $bestpos;

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

    public function getBestnr(): string
    {
        return $this->bestnr;
    }

    public function setBestnr(string $bestnr): self
    {
        $this->bestnr = $bestnr;
        return $this;
    }

    public function getBestpos(): int
    {
        return $this->bestpos;
    }

    public function setBestpos(int $bestpos): self
    {
        $this->bestpos = $bestpos;
        return $this;
    }
}
