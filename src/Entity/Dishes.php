<?php

namespace App\Entity;

use App\Repository\DishesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DishesRepository::class)]
class Dishes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dishes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'dishe', targetEntity: Variation::class, orphanRemoval: true)]
    private Collection $variations;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $caption = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $caption_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $caption_ca = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description_ca = null;

    #[ORM\Column]
    private ?int $order_by = null;

   

    public function __construct()
    {
        $this->variations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Variation>
     */
    public function getVariations(): Collection
    {
        return $this->variations;
    }

    public function addVariation(Variation $variation): self
    {
        if (!$this->variations->contains($variation)) {
            $this->variations[] = $variation;
            $variation->setDishe($this);
        }

        return $this;
    }

    public function removeVariation(Variation $variation): self
    {
        if ($this->variations->removeElement($variation)) {
            // set the owning side to null (unless already changed)
            if ($variation->getDishe() === $this) {
                $variation->setDishe(null);
            }
        }

        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    public function getCaptionEn(): ?string
    {
        return $this->caption_en;
    }

    public function setCaptionEn(?string $caption_en): self
    {
        $this->caption_en = $caption_en;

        return $this;
    }

    public function getCaptionCa(): ?string
    {
        return $this->caption_ca;
    }

    public function setCaptionCa(?string $caption_ca): self
    {
        $this->caption_ca = $caption_ca;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->description_en;
    }

    public function setDescriptionEn(?string $description_en): self
    {
        $this->description_en = $description_en;

        return $this;
    }

    public function getDescriptionCa(): ?string
    {
        return $this->description_ca;
    }

    public function setDescriptionCa(?string $description_ca): self
    {
        $this->description_ca = $description_ca;

        return $this;
    }

    public function getOrderBy(): ?int
    {
        return $this->order_by;
    }

    public function setOrderBy(int $order_by): self
    {
        $this->order_by = $order_by;

        return $this;
    }
    
}
