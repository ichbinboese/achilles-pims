<?php

namespace App\Entity\Easy;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "oxorder")]
class Oxorder
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 32)]
    private ?string $oxid;

    #[ORM\Column(type: 'int')]
    private int $oxshopid;

    #[ORM\Column(type: 'string', length: 32)]
    private ?string $oxuserid;

    #[ORM\Column(type: '\DateTime')]
    private ?\DateTime $oxorderdate;

    #[ORM\Column(type: 'string', length: 128)]
    private ?string $oxordernr;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxbillcompany;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxbillemail;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxbillfname;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxbilllname;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxbillstreet;

    #[ORM\Column(type: 'string', length: 16)]
    private ?string $oxbillstreetnr;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxbilladdinfo;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxbillustid;

    #[ORM\Column(type: 'bool')]
    private bool $oxbillustidstatus;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxbillcity;

    #[ORM\Column(type: 'string', length: 32)]
    private ?string $oxbillcountryid;

    #[ORM\Column(type: 'string', length: 32)]
    private ?string $oxbillstateid;

    #[ORM\Column(type: 'string', length: 16)]
    private ?string $oxbillzip;

    #[ORM\Column(type: 'string', length: 128)]
    private ?string $oxbillfon;

    #[ORM\Column(type: 'string', length: 128)]
    private ?string $oxbillfax;

    #[ORM\Column(type: 'string', length: 128)]
    private ?string $oxbillsal;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxdelcompany;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxdelfname;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxdellname;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxdelstreet;

    #[ORM\Column(type: 'string', length: 16)]
    private ?string $oxdelstreetnr;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxdeladdinfo;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $oxdelcity;

    #[ORM\Column(type: 'string', length: 32)]
    private ?string $oxdelcountryid;

    #[ORM\Column(type: 'string', length: 32)]
    private ?string $oxdelstateid;

    #[ORM\Column(type: 'string', length: 16)]
    private ?string $oxdelzip;

    #[ORM\Column(type: 'string', length: 128)]
    private ?string $oxdelfon;

    #[ORM\Column(type: 'string', length: 128)]
    private ?string $oxdelfax;

    #[ORM\Column(type: 'string', length: 128)]
    private ?string $oxdelsal;

    #[ORM\Column(type: 'string', length: 32)]
    private ?string $oxpaymentid;

    #[ORM\Column(type: 'string', length: 32)]
    private ?string $oxpaymenttype;

    #[ORM\Column(type: 'float')]
    private float $oxtotalnetsum;

    #[ORM\Column(type: 'float')]
    private float $oxtotalbrutsum;

    #[ORM\Column(type: 'float')]
    private float $oxtotalordersum;

    #[ORM\Column(type: 'float')]
    private float $oxartvat1;

    #[ORM\Column(type: 'float')]
    private float $oxartvatprice1;

    #[ORM\Column(type: 'float')]
    private float $oxartvat2;

    #[ORM\Column(type: 'float')]
    private float $oxartvatprice2;

    #[ORM\Column(type: "string", nullable: true)]
    private $waerpauftragnr;

    public function getOxid(): ?string
    {
        return $this->oxid;
    }

    public function setOxid(?string $oxid): self
    {
        return $this->oxid = $oxid;
    }

    public function getOxshopid(): int
    {
        return $this->oxshopid;
    }

    public function setOxshopid(int $oxshopid): self
    {
        return $this->oxshopid = $oxshopid;
    }

    public function getOxuserid(): ?string
    {
        return $this->oxuserid;
    }

    public function setOxuserid(?string $oxuserid): self
    {
        return $this->oxuserid = $oxuserid;
    }

    public function getOxorderdate(): ?\DateTime
    {
        return $this->oxorderdate;
    }

    public function setOxorderdate(?\DateTime $oxorderdate): self
    {
        return $this->oxorderdate = $oxorderdate;
    }

    public function getOxordernr(): ?string
    {
        return $this->oxordernr;
    }

    public function setOxordernr(?string $oxordernr): self
    {
        return $this->oxordernr = $oxordernr;
    }

    public function getOxbillcompany(): ?string
    {
        return $this->oxbillcompany;
    }

    public function setOxbillcompany(?string $oxbillcompany): self
    {
        return $this->oxbillcompany = $oxbillcompany;
    }

    public function getOxbillemail(): ?string
    {
        return $this->oxbillemail;
    }

    public function setOxbillemail(?string $oxbillemail): self
    {
        return $this->oxbillemail = $oxbillemail;
    }

    public function getOxbillfname(): ?string
    {
        return $this->oxbillfname;
    }

    public function setOxbillfname(?string $oxbillfname): self
    {
        return $this->oxbillfname = $oxbillfname;
    }

    public function getOxbilllname(): ?string
    {
        return $this->oxbilllname;
    }

    public function setOxbilllname(?string $oxbilllname): self
    {
        return $this->oxbilllname = $oxbilllname;
    }

    public function getOxbillstreet(): ?string
    {
        return $this->oxbillstreet;
    }

    public function setOxbillstreet(?string $oxbillstreet): self
    {
        return $this->oxbillstreet = $oxbillstreet;
    }

    public function getOxbillstreetnr(): ?string
    {
        return $this->oxbillstreetnr;
    }

    public function setOxbillstreetnr(?string $oxbillstreetnr): self
    {
        return $this->oxbillstreetnr = $oxbillstreetnr;
    }

    public function getOxbilladdinfo(): ?string
    {
        return $this->oxbilladdinfo;
    }

    public function setOxbilladdinfo(?string $oxbilladdinfo): self
    {
        return $this->oxbilladdinfo = $oxbilladdinfo;
    }

    public function getOxbillustid(): ?string
    {
        return $this->oxbillustid;
    }

    public function setOxbillustid(?string $oxbillustid): self
    {
        return $this->oxbillustid = $oxbillustid;
    }

    public function isOxbillustidstatus(): bool
    {
        return $this->oxbillustidstatus;
    }

    public function setOxbillustidstatus(bool $oxbillustidstatus): self
    {
        return $this->oxbillustidstatus = $oxbillustidstatus;
    }

    public function getOxbillcity(): ?string
    {
        return $this->oxbillcity;
    }

    public function setOxbillcity(?string $oxbillcity): self
    {
        return $this->oxbillcity = $oxbillcity;
    }

    public function getOxbillcountryid(): ?string
    {
        return $this->oxbillcountryid;
    }

    public function setOxbillcountryid(?string $oxbillcountryid): self
    {
        return $this->oxbillcountryid = $oxbillcountryid;
    }

    public function getOxbillstateid(): ?string
    {
        return $this->oxbillstateid;
    }

    public function setOxbillstateid(?string $oxbillstateid): self
    {
        return $this->oxbillstateid = $oxbillstateid;
    }

    public function getOxbillzip(): ?string
    {
        return $this->oxbillzip;
    }

    public function setOxbillzip(?string $oxbillzip): self
    {
        return $this->oxbillzip = $oxbillzip;
    }

    public function getOxbillfon(): ?string
    {
        return $this->oxbillfon;
    }

    public function setOxbillfon(?string $oxbillfon): self
    {
        return $this->oxbillfon = $oxbillfon;
    }

    public function getOxbillfax(): ?string
    {
        return $this->oxbillfax;
    }

    public function setOxbillfax(?string $oxbillfax): self
    {
        return $this->oxbillfax = $oxbillfax;
    }

    public function getOxbillsal(): ?string
    {
        return $this->oxbillsal;
    }

    public function setOxbillsal(?string $oxbillsal): self
    {
        return $this->oxbillsal = $oxbillsal;
    }

    public function getOxdelcompany(): ?string
    {
        return $this->oxdelcompany;
    }

    public function setOxdelcompany(?string $oxdelcompany): self
    {
        return $this->oxdelcompany = $oxdelcompany;
    }

    public function getOxdelfname(): ?string
    {
        return $this->oxdelfname;
    }

    public function setOxdelfname(?string $oxdelfname): self
    {
        return $this->oxdelfname = $oxdelfname;
    }

    public function getOxdellname(): ?string
    {
        return $this->oxdellname;
    }

    public function setOxdellname(?string $oxdellname): self
    {
        return $this->oxdellname = $oxdellname;
    }

    public function getOxdelstreet(): ?string
    {
        return $this->oxdelstreet;
    }

    public function setOxdelstreet(?string $oxdelstreet): self
    {
        return $this->oxdelstreet = $oxdelstreet;
    }

    public function getOxdelstreetnr(): ?string
    {
        return $this->oxdelstreetnr;
    }

    public function setOxdelstreetnr(?string $oxdelstreetnr): self
    {
        return $this->oxdelstreetnr = $oxdelstreetnr;
    }

    public function getOxdeladdinfo(): ?string
    {
        return $this->oxdeladdinfo;
    }

    public function setOxdeladdinfo(?string $oxdeladdinfo): self
    {
        return $this->oxdeladdinfo = $oxdeladdinfo;
    }

    public function getOxdelcity(): ?string
    {
        return $this->oxdelcity;
    }

    public function setOxdelcity(?string $oxdelcity): self
    {
        return $this->oxdelcity = $oxdelcity;
    }

    public function getOxdelcountryid(): ?string
    {
        return $this->oxdelcountryid;
    }

    public function setOxdelcountryid(?string $oxdelcountryid): self
    {
        return $this->oxdelcountryid = $oxdelcountryid;
    }

    public function getOxdelstateid(): ?string
    {
        return $this->oxdelstateid;
    }

    public function setOxdelstateid(?string $oxdelstateid): self
    {
        return $this->oxdelstateid = $oxdelstateid;
    }

    public function getOxdelzip(): ?string
    {
        return $this->oxdelzip;
    }

    public function setOxdelzip(?string $oxdelzip): self
    {
        return $this->oxdelzip = $oxdelzip;
    }

    public function getOxdelfon(): ?string
    {
        return $this->oxdelfon;
    }

    public function setOxdelfon(?string $oxdelfon): self
    {
        return $this->oxdelfon = $oxdelfon;
    }

    public function getOxdelfax(): ?string
    {
        return $this->oxdelfax;
    }

    public function setOxdelfax(?string $oxdelfax): self
    {
        return $this->oxdelfax = $oxdelfax;
    }

    public function getOxdelsal(): ?string
    {
        return $this->oxdelsal;
    }

    public function setOxdelsal(?string $oxdelsal): self
    {
        return $this->oxdelsal = $oxdelsal;
    }

    public function getOxpaymentid(): ?string
    {
        return $this->oxpaymentid;
    }

    public function setOxpaymentid(?string $oxpaymentid): self
    {
        return $this->oxpaymentid = $oxpaymentid;
    }

    public function getOxpaymenttype(): ?string
    {
        return $this->oxpaymenttype;
    }

    public function setOxpaymenttype(?string $oxpaymenttype): self
    {
        return $this->oxpaymenttype = $oxpaymenttype;
    }

    public function getOxtotalnetsum(): float
    {
        return $this->oxtotalnetsum;
    }

    public function setOxtotalnetsum(float $oxtotalnetsum): self
    {
        return $this->oxtotalnetsum = $oxtotalnetsum;
    }

    public function getOxtotalbrutsum(): float
    {
        return $this->oxtotalbrutsum;
    }

    public function setOxtotalbrutsum(float $oxtotalbrutsum): self
    {
        return $this->oxtotalbrutsum = $oxtotalbrutsum;
    }

    public function getOxtotalordersum(): float
    {
        return $this->oxtotalordersum;
    }

    public function setOxtotalordersum(float $oxtotalordersum): self
    {
        return $this->oxtotalordersum = $oxtotalordersum;
    }

    public function getOxartvat1(): float
    {
        return $this->oxartvat1;
    }

    public function setOxartvat1(float $oxartvat1): self
    {
        return $this->oxartvat1 = $oxartvat1;
    }

    public function getOxartvatprice1(): float
    {
        return $this->oxartvatprice1;
    }

    public function setOxartvatprice1(float $oxartvatprice1): self
    {
        return $this->oxartvatprice1 = $oxartvatprice1;
    }

    public function getOxartvat2(): float
    {
        return $this->oxartvat2;
    }

    public function setOxartvat2(float $oxartvat2): self
    {
        return $this->oxartvat2 = $oxartvat2;
    }

    public function getOxartvatprice2(): float
    {
        return $this->oxartvatprice2;
    }

    public function setOxartvatprice2(float $oxartvatprice2): self
    {
        return $this->oxartvatprice2 = $oxartvatprice2;
    }

    public function getWaerpauftragnr()
    {
        return $this->waerpauftragnr;
    }

    public function setWaerpauftragnr($waerpauftragnr): self
    {
        return $this->waerpauftragnr = $waerpauftragnr;
    }

}
