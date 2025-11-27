<?php

namespace App\Entity;

use App\Repository\PublicationFragmentRepository;
use App\Sowapps\SoIngenious\FragmentRouting;
use App\Sowapps\SoIngenious\PublicationStatus;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicationFragmentRepository::class)]
class PublicationFragment extends Fragment {
    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, enumType: PublicationStatus::class)]
    private ?PublicationStatus $status = null;

    #[ORM\Column(type: 'datetimetz')]
    protected ?DateTime $publishDate = null;

    #[ORM\Column(length: 255, nullable: true, enumType: FragmentRouting::class)]
    private ?FragmentRouting $routing = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 511, nullable: true)]
    private ?string $excerpt = null;

    public function getSlug(): ?string {
        return $this->slug;
    }

    public function setSlug(string $slug): static {
        $this->slug = $slug;

        return $this;
    }

    public function getStatus(): ?PublicationStatus {
        return $this->status;
    }

    public function setStatus(?PublicationStatus $status): PublicationFragment {
        $this->status = $status;
        return $this;
    }

    public function getRouting(): ?FragmentRouting {
        return $this->routing;
    }

    public function setRouting(?FragmentRouting $routing): PublicationFragment {
        $this->routing = $routing;
        return $this;
    }

    public function getPublishDate(): ?DateTime {
        return $this->publishDate;
    }

    public function setPublishDate(?DateTime $publishDate): Fragment {
        $this->publishDate = $publishDate;
        return $this;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(string $title): static {
        $this->title = $title;

        return $this;
    }

    public function getExcerpt(): ?string {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): static {
        $this->excerpt = $excerpt;

        return $this;
    }
}
