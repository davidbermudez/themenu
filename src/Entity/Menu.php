<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'menus')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $business = null;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: Dishes::class, orphanRemoval: true)]
    private Collection $dishes;

    #[ORM\Column]
    private ?bool $lang_es = null;

    #[ORM\Column]
    private ?bool $lang_en = null;

    #[ORM\Column]
    private ?bool $lang_ca = null;

    #[ORM\Column(length: 8)]
    private ?string $qr_code = null;

    #[ORM\Column(length: 255)]
    private ?string $caption = null;

    public function __construct()
    {
        $this->dishes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBusiness(): ?Business
    {
        return $this->business;
    }

    public function setBusiness(?Business $business): self
    {
        $this->business = $business;

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
            $dish->setMenu($this);
        }

        return $this;
    }

    public function removeDish(Dishes $dish): self
    {
        if ($this->dishes->removeElement($dish)) {
            // set the owning side to null (unless already changed)
            if ($dish->getMenu() === $this) {
                $dish->setMenu(null);
            }
        }

        return $this;
    }

    public function isLangEs(): ?bool
    {
        return $this->lang_es;
    }

    public function setLangEs(bool $lang_es): self
    {
        $this->lang_es = $lang_es;

        return $this;
    }

    public function isLangEn(): ?bool
    {
        return $this->lang_en;
    }

    public function setLangEn(bool $lang_en): self
    {
        $this->lang_en = $lang_en;

        return $this;
    }

    public function isLangCa(): ?bool
    {
        return $this->lang_ca;
    }

    public function setLangCa(bool $lang_ca): self
    {
        $this->lang_ca = $lang_ca;

        return $this;
    }

    public function getQrCode(): ?string
    {
        return $this->qr_code;
    }

    public function setQrCode(string $qr_code): self
    {
        $this->qr_code = $qr_code;

        return $this;
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
}
