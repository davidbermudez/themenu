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
    private ?bool $lang_01 = null;

    #[ORM\Column]
    private ?bool $lang_02 = null;

    #[ORM\Column]
    private ?bool $lang_03 = null;

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

    public function isLang01(): ?bool
    {
        return $this->lang_01;
    }

    public function setLang01(bool $lang_01): self
    {
        $this->lang_01 = $lang_01;

        return $this;
    }

    public function islang02(): ?bool
    {
        return $this->lang_02;
    }

    public function setlang02(bool $lang_02): self
    {
        $this->lang_02 = $lang_02;

        return $this;
    }

    public function islang03(): ?bool
    {
        return $this->lang_03;
    }

    public function setlang03(bool $lang_03): self
    {
        $this->lang_03 = $lang_03;

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
