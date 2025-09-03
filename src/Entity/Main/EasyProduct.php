<?php
declare(strict_types=1);

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EasyProduct
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
    private ?string $oxOrderNr;

    #[ORM\Column]
    private ?int $ddPosition;

    #[ORM\ManyToOne(targetEntity: EasyOrder::class, inversedBy: 'products')]
    private ?EasyOrder $order;

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

    public function getOxOrderNr(): ?string
    {
        return $this->oxOrderNr;
    }

    public function setOxOrderNr(string $oxOrderNr): self
    {
        $this->oxOrderNr = $oxOrderNr;
        return $this;
    }

    public function getDdPosition(): ?int
    {
        return $this->ddPosition;
    }

    public function setDdPosition(int $ddPosition): self
    {
        $this->ddPosition = $ddPosition;
        return $this;
    }

    public function getOrder(): ?EasyOrder
    {
        return $this->order;
    }

    public function setOrder(?EasyOrder $order): self
    {
        $this->order = $order;
        return $this;
    }
}
