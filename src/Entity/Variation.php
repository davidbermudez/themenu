<?php

namespace App\Entity;

use App\Repository\VariationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VariationRepository::class)]
class Variation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'variations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dishes $dishe = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $name_02 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $name_03 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDishe(): ?Dishes
    {
        return $this->dishe;
    }

    public function setDishe(?Dishes $dishe): self
    {
        $this->dishe = $dishe;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName02(): ?string
    {
        return $this->name_02;
    }

    public function setName02(?string $name_02): self
    {
        $this->name_02 = $name_02;

        return $this;
    }

    public function getName03(): ?string
    {
        return $this->name_03;
    }

    public function setName03(?string $name_03): self
    {
        $this->name_03 = $name_03;

        return $this;
    }
}
