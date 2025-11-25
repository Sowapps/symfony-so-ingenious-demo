<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use App\Sowapps\SoIngenious\PublicationStatus;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Entity\AbstractEntity;
use Sowapps\SoCore\Entity\Language;

/**
 * TODO Determine if generic content entity isn't better ? Same entity for testimonials, gallery image or any simple content.
 */
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Article extends AbstractEntity {
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, enumType: PublicationStatus::class)]
    private ?PublicationStatus $status = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fragment $fragment = null;
    // TODO Add multiple facet with a OneToMany relation with Fragment, so we could have a single page fragment, summary fragment, ...

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?LocalizedUnit $localizedUnit = null;

    #[ORM\PostPersist]
    public function onPostPersist(PostPersistEventArgs $args): void {
        // Add page to the related list of the fragment
        $this->fragment->setRelated('article', $this);
        $args->getObjectManager()->flush();
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(string $title): static {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string {
        return $this->slug;
    }

    public function setSlug(?string $slug): Article {
        $this->slug = $slug;
        return $this;
    }

    public function getStatus(): ?PublicationStatus {
        return $this->status;
    }

    public function setStatus(?PublicationStatus $status): Article {
        $this->status = $status;
        return $this;
    }

    public function getLanguage(): ?Language {
        return $this->language;
    }

    public function setLanguage(?Language $language): static {
        $this->language = $language;

        return $this;
    }

    public function getFragment(): ?Fragment {
        return $this->fragment;
    }

    public function setFragment(?Fragment $fragment): static {
        $this->fragment = $fragment;

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
