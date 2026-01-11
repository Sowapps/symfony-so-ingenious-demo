<?php

namespace App\Entity;

use App\Repository\SlotFragmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Core\Entity\AbstractEntity;

/**
 * Link between hardcoded slot and fragment
 * Use
 */
#[ORM\Entity(repositoryClass: SlotFragmentRepository::class)]
class SlotFragment extends AbstractEntity {
    /**
     * Slot name present in template file to include fragment by arbitrary name
     */
    #[ORM\Column(length: 255)]
    private ?string $slot = null;

    /**
     * Link slot to any fragment according to the current language
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?LocalizedUnit $fragmentUnit = null;

    public function getSlot(): ?string {
        return $this->slot;
    }

    public function setSlot(string $slot): static {
        $this->slot = $slot;

        return $this;
    }

    public function getFragmentUnit(): ?LocalizedUnit {
        return $this->fragmentUnit;
    }

    public function setFragmentUnit(?LocalizedUnit $fragmentUnit): static {
        $this->fragmentUnit = $fragmentUnit;

        return $this;
    }
}
