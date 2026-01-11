<?php

namespace App\Entity;

use App\Repository\FragmentReferenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Core\Entity\AbstractEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Inline fragment reference of another fragment
 */
#[ORM\Entity(repositoryClass: FragmentReferenceRepository::class)]
#[UniqueEntity('name')]
class FragmentReference extends AbstractEntity {
    /** Reference is unique through all fragments */
    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'childReferences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fragment $parent = null;

    #[ORM\ManyToOne(inversedBy: 'parentReferences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fragment $child = null;

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): static {
        $this->name = $name;

        return $this;
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
}
