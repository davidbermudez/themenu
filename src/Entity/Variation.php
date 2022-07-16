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
}
