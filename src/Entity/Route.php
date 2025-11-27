<?php

namespace App\Entity;

use App\Repository\RouteRepository;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Entity\AbstractEntity;
use Sowapps\SoCore\Entity\Language;

#[ORM\Entity(repositoryClass: RouteRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'dtype', type: 'string')]
#[ORM\DiscriminatorMap(['route_fragment' => FragmentRoute::class, 'route_redirect' => RedirectRoute::class])]
abstract class Route extends AbstractEntity {
    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?LocalizedUnit $localizedUnit = null;

    public function getPath(): ?string {
        return $this->path;
    }

    public function setPath(string $path): static {
        $this->path = $path;

        return $this;
    }

    public function getLanguage(): ?Language {
        return $this->language;
    }

    public function setLanguage(?Language $language): Route {
        $this->language = $language;

        return $this;
    }

    public function getLocalizedUnit(): ?LocalizedUnit {
        return $this->localizedUnit;
    }

    public function setLocalizedUnit(?LocalizedUnit $localizedUnit): static {
        $this->localizedUnit = $localizedUnit;

        return $this;
    }
}
