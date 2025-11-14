<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Entity\AbstractEntity;
use Sowapps\SoCore\Entity\Language;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Page extends AbstractEntity
{
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fragment $fragment = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?LocalizedUnit $localizedUnit = null;

    #[ORM\PostPersist]
    public function onPostPersist(PostPersistEventArgs $args): void {
        // Add page to the related list of the fragment
        $this->fragment->setRelated('page', $this);
        $args->getObjectManager()->flush();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getLanguage(): ?Language {
        return $this->language;
    }

    public function setLanguage(?Language $language): static {
        $this->language = $language;

        return $this;
    }

    public function getFragment(): ?Fragment
    {
        return $this->fragment;
    }

    public function setFragment(?Fragment $fragment): static
    {
        $this->fragment = $fragment;

        return $this;
    }

    public function getLocalizedUnit(): ?LocalizedUnit
    {
        return $this->localizedUnit;
    }

    public function setLocalizedUnit(?LocalizedUnit $localizedUnit): static
    {
        $this->localizedUnit = $localizedUnit;

        return $this;
    }
}
