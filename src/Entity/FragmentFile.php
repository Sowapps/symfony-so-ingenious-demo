<?php

namespace App\Entity;

use App\Repository\FragmentFileRepository;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Core\Entity\AbstractEntity;
use Sowapps\SoCore\Entity\File;

#[ORM\Entity(repositoryClass: FragmentFileRepository::class)]
class FragmentFile extends AbstractEntity {
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    #[ORM\ManyToOne(inversedBy: 'fragmentFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fragment $fragment = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?File $file = null;

    public function isUnique(): bool {
        return $this->position === null;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): static {
        $this->name = $name;

        return $this;
    }

    public function getPosition(): ?int {
        return $this->position;
    }

    public function setPosition(?int $position): static {
        $this->position = $position;

        return $this;
    }

    public function getFragment(): ?Fragment {
        return $this->fragment;
    }

    public function setFragment(?Fragment $fragment): static {
        $this->fragment = $fragment;

        return $this;
    }

    public function getFile(): ?File {
        return $this->file;
    }

    public function setFile(?File $file): static {
        $this->file = $file;

        return $this;
    }
}
