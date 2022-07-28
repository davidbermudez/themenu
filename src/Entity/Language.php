<?php

namespace App\Entity;

use App\Repository\LanguageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LanguageRepository::class)]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 2)]
    private ?string $principal = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $lang_02 = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $lang_03 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrincipal(): ?string
    {
        return $this->principal;
    }

    public function setPrincipal(string $principal): self
    {
        $this->principal = $principal;

        return $this;
    }

    public function getLang02(): ?string
    {
        return $this->lang_02;
    }

    public function setLang02(?string $lang_02): self
    {
        $this->lang_02 = $lang_02;

        return $this;
    }

    public function getLang03(): ?string
    {
        return $this->lang_03;
    }

    public function setLang03(?string $lang_03): self
    {
        $this->lang_03 = $lang_03;

        return $this;
    }
}
