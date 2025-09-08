<?php
declare(strict_types=1);

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class APPProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $productId;

    #[ORM\Column(length: 255)]
    private ?string $productNr;

    #[ORM\Column(length: 255)]
    private ?string $appBestNr;

    #[ORM\Column]
    private ?int $bestPosition;

    #[ORM\ManyToOne(targetEntity: APPOrder::class, inversedBy: 'products')]
    private ?APPOrder $order;

    // Getter und Setter

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    public function getProductNr(): ?string
    {
        return $this->productNr;
    }

    public function setProductNr(string $productNr): self
    {
        $this->productNr = $productNr;
        return $this;
    }

    public function getAppBestNr(): ?string
    {
        return $this->appBestNr;
    }

    public function setAppBestNr(string $appBestNr): self
    {
        $this->appBestNr = $appBestNr;
        return $this;
    }

    public function getBestPosition(): ?int
    {
        return $this->bestPosition;
    }

    public function setBestPosition(int $bestPosition): self
    {
        $this->bestPosition = $bestPosition;
        return $this;
    }

    public function getOrder(): ?APPOrder
    {
        return $this->order;
    }

    public function setOrder(?APPOrder $order): self
    {
        $this->order = $order;
        return $this;
    }
}
