<?php

namespace App\Entity;

use App\Repository\LocalizedUnitRepository;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Entity\AbstractEntity;

#[ORM\Entity(repositoryClass: LocalizedUnitRepository::class)]
class LocalizedUnit extends AbstractEntity
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(?string $name): static {
        $this->name = $name;

        return $this;
    }
}
