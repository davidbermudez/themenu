<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Dishes::class, orphanRemoval: true)]
    private Collection $dishes;

    #[ORM\ManyToOne(inversedBy: 'category')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Menu $menu = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $caption_es = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $caption_en = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $caption_ca = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description_es = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description_ca = null;

    #[ORM\Column]
    private ?int $order_by = null;

    public function __construct()
    {
        $this->dishes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * @return Collection<int, Dishes>
     */
    public function getDishes(): Collection
    {
        return $this->dishes;
    }

    public function addDish(Dishes $dish): self
    {
        if (!$this->dishes->contains($dish)) {
            $this->dishes[] = $dish;
            $dish->setCategory($this);
        }

        return $this;
    }

    public function removeDish(Dishes $dish): self
    {
        if ($this->dishes->removeElement($dish)) {
            // set the owning side to null (unless already changed)
            if ($dish->getCategory() === $this) {
                $dish->setCategory(null);
            }
        }

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

        return $this;
    }

    public function getCaptionEs(): ?string
    {
        return $this->caption_es;
    }

    public function setCaptionEs(?string $caption_es): self
    {
        $this->caption_es = $caption_es;

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

    public function getDescriptionEs(): ?string
    {
        return $this->description_es;
    }

    public function setDescriptionEs(?string $description_es): self
    {
        $this->description_es = $description_es;

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
