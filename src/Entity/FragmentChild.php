<?php

namespace App\Entity;

use App\Repository\FragmentChildRepository;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Entity\AbstractEntity;

#[ORM\Entity(repositoryClass: FragmentChildRepository::class)]
class FragmentChild extends AbstractEntity
{
    #[ORM\ManyToOne(inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fragment $parentFragment = null;

    #[ORM\ManyToOne(inversedBy: 'parents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fragment $childFragment = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    public function getParentFragment(): ?Fragment
    {
        return $this->parentFragment;
    }

    public function setParentFragment(?Fragment $parentFragment): static
    {
        $this->parentFragment = $parentFragment;

        return $this;
    }

    public function getChildFragment(): ?Fragment
    {
        return $this->childFragment;
    }

    public function setChildFragment(?Fragment $childFragment): static
    {
        $this->childFragment = $childFragment;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
