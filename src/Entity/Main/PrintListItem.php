<?php
declare(strict_types=1);

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'uniq_list_product', columns: ['printlist_id','easyproduct_id'])]
class PrintListItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PrintList::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PrintList $printList = null;

    #[ORM\ManyToOne(targetEntity: EasyProduct::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?EasyProduct $easyProduct = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrintList(): ?PrintList
    {
        return $this->printList;
    }

    public function setPrintList(PrintList $pl): self
    {
        $this->printList = $pl;
        return $this;
    }

    public function getEasyProduct(): ?EasyProduct
    {
        return $this->easyProduct;
    }

    public function setEasyProduct(EasyProduct $ep): self
    {
        $this->easyProduct = $ep;
        return $this;
    }
}
