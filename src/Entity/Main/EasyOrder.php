<?php
declare(strict_types=1);

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EasyOrder
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

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: EasyProduct::class)]
    private $products;

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
}
