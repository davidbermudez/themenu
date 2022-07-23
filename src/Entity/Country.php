<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name_en = null;

    #[ORM\Column(length: 50)]
    private ?string $name_es = null;

    #[ORM\Column(length: 2)]
    private ?string $iso2 = null;

    #[ORM\Column(length: 3)]
    private ?string $iso3 = null;

    #[ORM\Column(nullable: true)]
    private ?int $phone_code = null;

    #[ORM\Column(length: 50)]
    private ?string $name_fr = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameEn(): ?string
    {
        return $this->name_en;
    }

    public function setNameEn(string $name_en): self
    {
        $this->name_en = $name_en;

        return $this;
    }

    public function getNameEs(): ?string
    {
        return $this->name_es;
    }

    public function setNameEs(string $name_es): self
    {
        $this->name_es = $name_es;

        return $this;
    }

    public function getIso2(): ?string
    {
        return $this->iso2;
    }

    public function setIso2(string $iso2): self
    {
        $this->iso2 = $iso2;

        return $this;
    }

    public function getIso3(): ?string
    {
        return $this->iso3;
    }

    public function setIso3(string $iso3): self
    {
        $this->iso3 = $iso3;

        return $this;
    }

    public function getPhoneCode(): ?int
    {
        return $this->phone_code;
    }

    public function setPhoneCode(?int $phone_code): self
    {
        $this->phone_code = $phone_code;

        return $this;
    }

    public function getNameFr(): ?string
    {
        return $this->name_fr;
    }

    public function setNameFr(string $name_fr): self
    {
        $this->name_fr = $name_fr;

        return $this;
    }
}
