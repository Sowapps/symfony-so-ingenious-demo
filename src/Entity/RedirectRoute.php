<?php

namespace App\Entity;

use App\Repository\RedirectRouteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RedirectRouteRepository::class)]
class RedirectRoute extends Route {
    #[ORM\Column(length: 255)]
    private ?string $targetPath = null;

    public function getTargetPath(): ?string {
        return $this->targetPath;
    }

    public function setTargetPath(string $targetPath): static {
        $this->targetPath = $targetPath;

        return $this;
    }
}
