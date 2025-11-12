<?php

namespace App\Entity;

use App\Repository\FragmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Entity\AbstractEntity;
use Sowapps\SoCore\Entity\Language;

#[ORM\Entity(repositoryClass: FragmentRepository::class)]
class Fragment extends AbstractEntity {
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\Column]
    private array $properties = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $html = null;

    #[ORM\Column]
    private ?bool $snippet = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $snippetFragment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $templateName = null;

    /**
     * @var Collection<int, FragmentChild>
     */
    #[ORM\OneToMany(targetEntity: FragmentChild::class, mappedBy: 'parentFragment')]
    private Collection $children;

    /**
     * @var Collection<int, FragmentChild>
     */
    #[ORM\OneToMany(targetEntity: FragmentChild::class, mappedBy: 'childFragment')]
    private Collection $parents;

    public function __construct()
    {
        parent::__construct();

        $this->children = new ArrayCollection();
        $this->parents = new ArrayCollection();
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(?string $name): static {
        $this->name = $name;

        return $this;
    }

    public function getLanguage(): ?Language {
        return $this->language;
    }

    public function setLanguage(?Language $language): static {
        $this->language = $language;

        return $this;
    }

    public function getProperties(): array {
        return $this->properties;
    }

    public function setProperties(array $properties): static {
        $this->properties = $properties;

        return $this;
    }

    public function getHtml(): ?string {
        return $this->html;
    }

    public function setHtml(?string $html): static {
        $this->html = $html;

        return $this;
    }

    public function isSnippet(): ?bool {
        return $this->snippet;
    }

    public function setSnippet(bool $snippet): static {
        $this->snippet = $snippet;

        return $this;
    }

    public function getSnippetFragment(): ?self {
        return $this->snippetFragment;
    }

    public function setSnippetFragment(?self $snippetFragment): static {
        $this->snippetFragment = $snippetFragment;

        return $this;
    }

    public function getTemplateName(): ?string {
        return $this->templateName;
    }

    public function setTemplateName(?string $templateName): static {
        $this->templateName = $templateName;

        return $this;
    }

    /**
     * @return Collection<int, FragmentChild>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(FragmentChild $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParentFragment($this);
        }

        return $this;
    }

    public function removeChild(FragmentChild $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParentFragment() === $this) {
                $child->setParentFragment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FragmentChild>
     */
    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function addParent(FragmentChild $parent): static
    {
        if (!$this->parents->contains($parent)) {
            $this->parents->add($parent);
            $parent->setChildFragment($this);
        }

        return $this;
    }

    public function removeParent(FragmentChild $parent): static
    {
        if ($this->parents->removeElement($parent)) {
            // set the owning side to null (unless already changed)
            if ($parent->getChildFragment() === $this) {
                $parent->setChildFragment(null);
            }
        }

        return $this;
    }
}
