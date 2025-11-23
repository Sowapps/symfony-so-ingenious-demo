<?php

namespace App\Entity;

use App\Repository\FragmentLinkRepository;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Entity\AbstractEntity;

#[ORM\Entity(repositoryClass: FragmentLinkRepository::class)]
class FragmentLink extends AbstractEntity {
    #[ORM\ManyToOne(inversedBy: 'childLinks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fragment $parent = null;

    #[ORM\ManyToOne(inversedBy: 'parentLinks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fragment $child = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    public function isUnique(): bool {
        return $this->position === null;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getParent(): ?Fragment {
        return $this->parent;
    }

    public function setParent(?Fragment $parent): static {
        $this->parent = $parent;

        return $this;
    }

    public function getChild(): ?Fragment {
        return $this->child;
    }

    public function setChild(?Fragment $child): static {
        $this->child = $child;

        return $this;
    }

    public function getPosition(): ?int {
        return $this->position;
    }

    public function setPosition(?int $position): static {
        $this->position = $position;

        return $this;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): static {
        $this->name = $name;

        return $this;
    }
}
