<?php

namespace App\Entity;

use App\Repository\FragmentRepository;
use App\Sowapps\SoIngenious\TemplatePurpose;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Entity\AbstractEntity;
use Sowapps\SoCore\Entity\Language;

#[ORM\Entity(repositoryClass: FragmentRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'dtype', type: 'string')]
#[ORM\DiscriminatorMap(['fragment' => Fragment::class, 'fragment_publication' => PublicationFragment::class])]
class Fragment extends AbstractEntity {
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\Column]
    private array $properties = [];

    /**
     * Is this fragment reusable ?
     * @var bool|null
     */
    #[ORM\Column]
    private ?bool $snippet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $templateName = null;

    #[ORM\Column(length: 255, nullable: true, enumType: TemplatePurpose::class)]
    private ?TemplatePurpose $purpose = null;

    /**
     * Link fragments about same subjet but with a different language (Home => Home FR, Home EN)
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?LocalizedUnit $localizedUnit = null;

    /**
     * @var Collection<int, FragmentLink>
     */
    #[ORM\OneToMany(targetEntity: FragmentLink::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['name' => 'ASC', 'position' => 'ASC'])]
    private Collection $childLinks;

    /**
     * @var Collection<int, FragmentLink>
     */
    #[ORM\OneToMany(targetEntity: FragmentLink::class, mappedBy: 'child')]
    private Collection $parentLinks;

    #[ORM\OneToOne(mappedBy: 'fragment', cascade: ['persist', 'remove'])]
    private ?FragmentRoute $route = null;

    public function __construct()
    {
        parent::__construct();

        $this->childLinks = new ArrayCollection();
        $this->parentLinks = new ArrayCollection();
    }

    /**
     * @return array<string, Fragment>
     */
    public function getChildren(): array {
        $map = [];
        foreach( $this->getChildLinks() as $childLink ) {
            $child = $childLink->getChild();
            if( $childLink->isUnique() ) {
                $map[$childLink->getName()] = $child;
            } else {
                $map[$childLink->getName()] ??= [];
                $map[$childLink->getName()][$childLink->getPosition()] = $child;
            }
        }

        return $map;
    }

    public function setRelated($name, AbstractEntity $entity): void {
        $this->properties['_related'] ??= [];
        $this->properties['_related'][$name] = $entity->getEntityReference();
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

    public function isSnippet(): ?bool {
        return $this->snippet;
    }

    public function setSnippet(bool $snippet): static {
        $this->snippet = $snippet;

        return $this;
    }

    public function getTemplateName(): ?string {
        return $this->templateName;
    }

    public function setTemplateName(?string $templateName): static {
        $this->templateName = $templateName;

        return $this;
    }

    public function getPurpose(): ?TemplatePurpose {
        return $this->purpose;
    }

    public function setPurpose(?TemplatePurpose $purpose): static {
        $this->purpose = $purpose;

        return $this;
    }

    public function getLocalizedUnit(): ?LocalizedUnit {
        return $this->localizedUnit;
    }

    public function setLocalizedUnit(?LocalizedUnit $localizedUnit): static {
        $this->localizedUnit = $localizedUnit;

        return $this;
    }

    /**
     * @return Collection<int, FragmentLink>
     */
    public function getChildLinks(): Collection
    {
        return $this->childLinks;
    }

    public function addChildLink(FragmentLink $childLink): static
    {
        if( !$this->childLinks->contains($childLink) ) {
            $this->childLinks->add($childLink);
            $childLink->setParent($this);
        }

        return $this;
    }

    public function removeChildLink(FragmentLink $childLink): static
    {
        if( $this->childLinks->removeElement($childLink) ) {
            // set the owning side to null (unless already changed)
            if( $childLink->getParent() === $this ) {
                $childLink->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FragmentLink>
     */
    public function getParentLinks(): Collection
    {
        return $this->parentLinks;
    }

    public function addParentLink(FragmentLink $parentLink): static
    {
        if( !$this->parentLinks->contains($parentLink) ) {
            $this->parentLinks->add($parentLink);
            $parentLink->setChild($this);
        }

        return $this;
    }

    public function removeParentLink(FragmentLink $parentLink): static
    {
        if( $this->parentLinks->removeElement($parentLink) ) {
            // set the owning side to null (unless already changed)
            if( $parentLink->getChild() === $this ) {
                $parentLink->setChild(null);
            }
        }

        return $this;
    }

    public function getRoute(): ?FragmentRoute {
        return $this->route;
    }

    public function setRoute(?FragmentRoute $route): static {
        // unset the owning side of the relation if necessary
        if( $route === null && $this->route !== null ) {
            $this->route->setFragment(null);
        }

        // set the owning side of the relation if necessary
        if( $route !== null && $route->getFragment() !== $this ) {
            $route->setFragment($this);
        }

        $this->route = $route;

        return $this;
    }
}
