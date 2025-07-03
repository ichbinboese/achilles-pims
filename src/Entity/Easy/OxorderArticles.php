<?php

namespace App\Entity\Easy;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "oxorderarticles")]
class OxorderArticles
{
    #[ORM\Id]
    #[ORM\Column(type: "string", nullable: false)]
    private $oxid;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxorderid;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxamount;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxartid;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxartnum;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxtitle;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxshortdesc;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxselvariant;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxnetprice;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxbrutprice;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxvatprice;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxvat;
    #[ORM\Column(type: "text", nullable: false)]
    private $oxpersparam;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxprice;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxbprice;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxnprice;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxwrapid;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxexturl;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxurldesc;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxurlimg;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxthumb;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxpic1;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxpic2;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxpic3;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxpic4;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxpic5;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxweight;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxstock;
    #[ORM\Column(type: "date", nullable: false)]
    private $oxdelivery;
    #[ORM\Column(type: "date", nullable: false)]
    private $oxinsert;
    #[ORM\Column(type: "datetime", nullable: false)]
    private $oxtimestamp;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxlength;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxwidth;
    #[ORM\Column(type: "float", nullable: false)]
    private $oxheight;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxfile;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxsearchkeys;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxtemplate;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxquestionemail;
    #[ORM\Column(type: "boolean", nullable: false)]
    private $oxissearch;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxfolder;
    #[ORM\Column(type: "string", nullable: false)]
    private $oxsubclass;
    #[ORM\Column(type: "boolean", nullable: false)]
    private $oxstorno;
    #[ORM\Column(type: "integer", nullable: false)]
    private $oxordershopid;
    #[ORM\Column(type: "text", nullable: false)]
    private $oxerpstatus;
    #[ORM\Column(type: "boolean", nullable: false)]
    private $oxisbundle;
    #[ORM\Column(type: "text", nullable: false)]
    private $ddprintdata;
    #[ORM\Column(type: "integer", nullable: true)]
    private $ddposition;
    #[ORM\Column(type: "boolean", nullable: false)]
    private $ddindividual;
    #[ORM\Column(type: "float", nullable: false)]
    private $ecsbprice;
    #[ORM\Column(type: "string", nullable: true)]
    private $obility_session;
    #[ORM\Column(type: "text", nullable: true)]
    private $onlineeditor;
    #[ORM\Column(type: "string", nullable: true)]
    private $obility_order;
    #[ORM\Column(type: "string", nullable: true)]
    private $waerpauftragnr;

    public function getOxid()
    {
        return $this->oxid;
    }

    public function setOxid($oxid): self
    {
        return $this->oxid = $oxid;
    }

    public function getOxorderid()
    {
        return $this->oxorderid;
    }

    public function setOxorderid($oxorderid): self
    {
        return $this->oxorderid = $oxorderid;
    }

    public function getOxamount()
    {
        return $this->oxamount;
    }

    public function setOxamount($oxamount): self
    {
        return $this->oxamount = $oxamount;
    }

    public function getOxartid()
    {
        return $this->oxartid;
    }

    public function setOxartid($oxartid): self
    {
        return $this->oxartid = $oxartid;
    }

    public function getOxartnum()
    {
        return $this->oxartnum;
    }

    public function setOxartnum($oxartnum): self
    {
        return $this->oxartnum = $oxartnum;
    }

    public function getOxtitle()
    {
        return $this->oxtitle;
    }

    public function setOxtitle($oxtitle): self
    {
        return $this->oxtitle = $oxtitle;
    }

    public function getOxshortdesc()
    {
        return $this->oxshortdesc;
    }

    public function setOxshortdesc($oxshortdesc): self
    {
        return $this->oxshortdesc = $oxshortdesc;
    }

    public function getOxselvariant()
    {
        return $this->oxselvariant;
    }

    public function setOxselvariant($oxselvariant): self
    {
        return $this->oxselvariant = $oxselvariant;
    }

    public function getOxnetprice()
    {
        return $this->oxnetprice;
    }

    public function setOxnetprice($oxnetprice): self
    {
        return $this->oxnetprice = $oxnetprice;
    }

    public function getOxbrutprice()
    {
        return $this->oxbrutprice;
    }

    public function setOxbrutprice($oxbrutprice): self
    {
        return $this->oxbrutprice = $oxbrutprice;
    }

    public function getOxvatprice()
    {
        return $this->oxvatprice;
    }

    public function setOxvatprice($oxvatprice): self
    {
        return $this->oxvatprice = $oxvatprice;
    }

    public function getOxvat()
    {
        return $this->oxvat;
    }

    public function setOxvat($oxvat): self
    {
        return $this->oxvat = $oxvat;
    }

    public function getOxpersparam()
    {
        return $this->oxpersparam;
    }

    public function setOxpersparam($oxpersparam): self
    {
        return $this->oxpersparam = $oxpersparam;
    }

    public function getOxprice()
    {
        return $this->oxprice;
    }

    public function setOxprice($oxprice): self
    {
        return $this->oxprice = $oxprice;
    }

    public function getOxbprice()
    {
        return $this->oxbprice;
    }

    public function setOxbprice($oxbprice): self
    {
        return $this->oxbprice = $oxbprice;
    }

    public function getOxnprice()
    {
        return $this->oxnprice;
    }

    public function setOxnprice($oxnprice): self
    {
        return $this->oxnprice = $oxnprice;
    }

    public function getOxwrapid()
    {
        return $this->oxwrapid;
    }

    public function setOxwrapid($oxwrapid): self
    {
        return $this->oxwrapid = $oxwrapid;
    }

    public function getOxexturl()
    {
        return $this->oxexturl;
    }

    public function setOxexturl($oxexturl): self
    {
        return $this->oxexturl = $oxexturl;
    }

    public function getOxurldesc()
    {
        return $this->oxurldesc;
    }

    public function setOxurldesc($oxurldesc): self
    {
        return $this->oxurldesc = $oxurldesc;
    }

    public function getOxurlimg()
    {
        return $this->oxurlimg;
    }

    public function setOxurlimg($oxurlimg): self
    {
        return $this->oxurlimg = $oxurlimg;
    }

    public function getOxthumb()
    {
        return $this->oxthumb;
    }

    public function setOxthumb($oxthumb): self
    {
        return $this->oxthumb = $oxthumb;
    }

    public function getOxpic1()
    {
        return $this->oxpic1;
    }

    public function setOxpic1($oxpic1): self
    {
        return $this->oxpic1 = $oxpic1;
    }

    public function getOxpic2()
    {
        return $this->oxpic2;
    }

    public function setOxpic2($oxpic2): self
    {
        return $this->oxpic2 = $oxpic2;
    }

    public function getOxpic3()
    {
        return $this->oxpic3;
    }

    public function setOxpic3($oxpic3): self
    {
        return $this->oxpic3 = $oxpic3;
    }

    public function getOxpic4()
    {
        return $this->oxpic4;
    }

    public function setOxpic4($oxpic4): self
    {
        return $this->oxpic4 = $oxpic4;
    }

    public function getOxpic5()
    {
        return $this->oxpic5;
    }

    public function setOxpic5($oxpic5): self
    {
        return $this->oxpic5 = $oxpic5;
    }

    public function getOxweight()
    {
        return $this->oxweight;
    }

    public function setOxweight($oxweight): self
    {
        return $this->oxweight = $oxweight;
    }

    public function getOxstock()
    {
        return $this->oxstock;
    }

    public function setOxstock($oxstock): self
    {
        return $this->oxstock = $oxstock;
    }

    public function getOxdelivery()
    {
        return $this->oxdelivery;
    }

    public function setOxdelivery($oxdelivery): self
    {
        return $this->oxdelivery = $oxdelivery;
    }

    public function getOxinsert()
    {
        return $this->oxinsert;
    }

    public function setOxinsert($oxinsert): self
    {
        return $this->oxinsert = $oxinsert;
    }

    public function getOxtimestamp()
    {
        return $this->oxtimestamp;
    }

    public function setOxtimestamp($oxtimestamp): self
    {
        return $this->oxtimestamp = $oxtimestamp;
    }

    public function getOxlength()
    {
        return $this->oxlength;
    }

    public function setOxlength($oxlength): self
    {
        return $this->oxlength = $oxlength;
    }

    public function getOxwidth()
    {
        return $this->oxwidth;
    }

    public function setOxwidth($oxwidth): self
    {
        return $this->oxwidth = $oxwidth;
    }

    public function getOxheight()
    {
        return $this->oxheight;
    }

    public function setOxheight($oxheight): self
    {
        return $this->oxheight = $oxheight;
    }

    public function getOxfile()
    {
        return $this->oxfile;
    }

    public function setOxfile($oxfile): self
    {
        return $this->oxfile = $oxfile;
    }

    public function getOxsearchkeys()
    {
        return $this->oxsearchkeys;
    }

    public function setOxsearchkeys($oxsearchkeys): self
    {
        return $this->oxsearchkeys = $oxsearchkeys;
    }

    public function getOxtemplate()
    {
        return $this->oxtemplate;
    }

    public function setOxtemplate($oxtemplate): self
    {
        return $this->oxtemplate = $oxtemplate;
    }

    public function getOxquestionemail()
    {
        return $this->oxquestionemail;
    }

    public function setOxquestionemail($oxquestionemail): self
    {
        return $this->oxquestionemail = $oxquestionemail;
    }

    public function getOxissearch()
    {
        return $this->oxissearch;
    }

    public function setOxissearch($oxissearch): self
    {
        return $this->oxissearch = $oxissearch;
    }

    public function getOxfolder()
    {
        return $this->oxfolder;
    }

    public function setOxfolder($oxfolder): self
    {
        return $this->oxfolder = $oxfolder;
    }

    public function getOxsubclass()
    {
        return $this->oxsubclass;
    }

    public function setOxsubclass($oxsubclass): self
    {
        return $this->oxsubclass = $oxsubclass;
    }

    public function getOxstorno()
    {
        return $this->oxstorno;
    }

    public function setOxstorno($oxstorno): self
    {
        return $this->oxstorno = $oxstorno;
    }

    public function getOxordershopid()
    {
        return $this->oxordershopid;
    }

    public function setOxordershopid($oxordershopid): self
    {
        return $this->oxordershopid = $oxordershopid;
    }

    public function getOxerpstatus()
    {
        return $this->oxerpstatus;
    }

    public function setOxerpstatus($oxerpstatus): self
    {
        return $this->oxerpstatus = $oxerpstatus;
    }

    public function getOxisbundle()
    {
        return $this->oxisbundle;
    }

    public function setOxisbundle($oxisbundle): self
    {
        return $this->oxisbundle = $oxisbundle;
    }

    public function getDdprintdata()
    {
        return $this->ddprintdata;
    }

    public function setDdprintdata($ddprintdata): self
    {
        return $this->ddprintdata = $ddprintdata;
    }

    public function getDdposition()
    {
        return $this->ddposition;
    }

    public function setDdposition($ddposition): self
    {
        return $this->ddposition = $ddposition;
    }

    public function getDdindividual()
    {
        return $this->ddindividual;
    }

    public function setDdindividual($ddindividual): self
    {
        return $this->ddindividual = $ddindividual;
    }

    public function getEcsbprice()
    {
        return $this->ecsbprice;
    }

    public function setEcsbprice($ecsbprice): self
    {
        return $this->ecsbprice = $ecsbprice;
    }

    public function getObilitySession()
    {
        return $this->obility_session;
    }

    public function setObilitySession($obility_session): self
    {
        return $this->obility_session = $obility_session;
    }

    public function getOnlineeditor()
    {
        return $this->onlineeditor;
    }

    public function setOnlineeditor($onlineeditor): self
    {
        return $this->onlineeditor = $onlineeditor;
    }

    public function getObilityOrder()
    {
        return $this->obility_order;
    }

    public function setObilityOrder($obility_order): self
    {
        return $this->obility_order = $obility_order;
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
