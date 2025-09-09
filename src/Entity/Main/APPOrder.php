<?php
declare(strict_types=1);

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class APPOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $orderId;

    #[ORM\Column(length: 255)]
    private ?string $orderNr;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $status;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: APPProduct::class)]
    private $products;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $appbestnr;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $appposnr;

    // Getter und Setter

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function getOrderNr(): ?string
    {
        return $this->orderNr;
    }

    public function setOrderNr(string $orderNr): self
    {
        $this->orderNr = $orderNr;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function setProducts($products): self
    {
        $this->products = $products;
        return $this;
    }

    public function getAppbestnr(): ?string
    {
        return $this->appbestnr;
    }

    public function setAppbestnr(?string $appbestnr): self
    {
        $this->appbestnr = $appbestnr;
        return $this;
    }

    public function getAppposnr(): ?string
    {
        return $this->appposnr;
    }

    public function setAppposnr(?string $appposnr): self
    {
        $this->appposnr = $appposnr;
        return $this;
    }


}
