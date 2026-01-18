<?php

namespace App\Entity;

use App\Repository\FragmentRepository;
use App\Sowapps\SoIngenious\TemplatePurpose;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sowapps\SoCore\Entity\AbstractEntity;
use Sowapps\SoCore\Entity\File;
use Sowapps\SoCore\Entity\Language;

#[ORM\Entity(repositoryClass: FragmentRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'dtype', type: 'string')]
#[ORM\DiscriminatorMap(['fragment' => Fragment::class, 'fragment_publication' => PublicationFragment::class])]
class Fragment extends AbstractEntity {
    #[ORM\Column(length: 255)]
    private ?string $name = null;

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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    /**
     * Links to other child fragments where this fragment is the parent
     * @var Collection<int, FragmentLink>
     */
    #[ORM\OneToMany(targetEntity: FragmentLink::class, mappedBy: 'parent', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['name' => 'ASC', 'position' => 'ASC'])]
    private Collection $childLinks;

    /**
     * Links to other parent fragments where this fragment is the child
     * @var Collection<int, FragmentLink>
     */
    #[ORM\OneToMany(targetEntity: FragmentLink::class, mappedBy: 'child')]
    private Collection $parentLinks;

    /**
     * Inline references to other child fragments where this fragment is the parent
     * @var Collection<int, FragmentReference>
     */
    #[ORM\OneToMany(targetEntity: FragmentReference::class, mappedBy: 'parent', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $childReferences;

    /**
     * Inline references to other parent fragments where this fragment is the child
     * @var Collection<int, FragmentReference>
     */
    #[ORM\OneToMany(targetEntity: FragmentReference::class, mappedBy: 'child')]
    private Collection $parentReferences;

    #[ORM\OneToOne(mappedBy: 'fragment', cascade: ['persist', 'remove'])]
    private ?FragmentRoute $route = null;

    /**
     * @var Collection<int, FragmentFile>
     */
    #[ORM\OneToMany(targetEntity: FragmentFile::class, mappedBy: 'fragment', cascade: ['persist', 'remove'])]
    private Collection $fragmentFiles;

    public function __construct()
    {
        parent::__construct();

        $this->childLinks = new ArrayCollection();
        $this->parentLinks = new ArrayCollection();
        $this->childReferences = new ArrayCollection();
        $this->parentReferences = new ArrayCollection();
        $this->fragmentFiles = new ArrayCollection();
    }

    /**
     * Set children using array for Fixtures
     * @param array $children
     * @return Fragment
     * @internal For Fixtures only
     */
    public function setChildren(array $children): static {
        $this->childLinks->clear();
        foreach( $children as $name => $nameChildren ) {
            // $nameChildren is an array of children with same name
            // If this is an array, we provide a position, cause isUnique() is base on it
            $isList = is_array($nameChildren);
            if( !$isList ) {
                $nameChildren = [$nameChildren];
            }
            $position = 0;
            foreach( $nameChildren as $child ) {
                $link = new FragmentLink();
                $link->setName($name);
                $link->setChild($child);
                $this->addChildLink($link);
                if( $isList ) {
                    $link->setPosition($position++);
                }
            }
        }

        return $this;
    }

    /**
     * Set child references using array for Fixtures
     * @param array $references
     * @return Fragment
     * @internal For Fixtures only
     */
    public function setReferences(array $references): static {
        $this->childReferences->clear();
        foreach( $references as $childName => $childFragment ) {
            $reference = new FragmentReference();
            $reference->setName($childName);
            $reference->setChild($childFragment);
            $this->addChildReference($reference);
        }

        return $this;
    }

    /**
     * Set files using array for Fixtures
     * @param array $files
     * @return Fragment
     * @internal For Fixtures only
     */
    public function setFiles(array $files): static {
        $this->fragmentFiles->clear();
        foreach( $files as $name => $nameFiles ) {
            // $nameFiles is an array of children with same name
            // If this is an array, we provide a position, cause isUnique() is base on it
            $isList = is_array($nameFiles);
            if( !$isList ) {
                $nameFiles = [$nameFiles];
            }
            $position = 0;
            foreach( $nameFiles as $file ) {
                $fragmentFile = new FragmentFile();
                $fragmentFile->setName($name);
                $fragmentFile->setFile($file);
                $this->addFragmentFile($fragmentFile);
                if( $isList ) {
                    $file->setPosition($position++);
                }
            }
        }

        return $this;
    }

    /**
     * @return array<string, File>
     */
    public function getFiles(): array {
        $map = [];
        foreach( $this->getFragmentFiles() as $fragmentFile ) {
            $file = $fragmentFile->getFile();
            $name = $fragmentFile->getName();
            if( $fragmentFile->isUnique() ) {
                $map[$name] = $file;
            } else {
                $map[$name] ??= [];
                $map[$name][$fragmentFile->getPosition()] = $file;
            }
        }

        return $map;
    }

    /**
     * @return array<string, Fragment>
     */
    public function getChildren(): array {
        $map = [];
        foreach( $this->getChildLinks() as $childLink ) {
            $child = $childLink->getChild();
            $name = $childLink->getName();
            if( $childLink->isUnique() ) {
                $map[$name] = $child;
            } else {
                $map[$name] ??= [];
                $map[$name][$childLink->getPosition()] = $child;
            }
        }

        return $map;
    }

    /**
     * @return array<string, Fragment>
     */
    public function getReferences(): array {
        $map = [];
        foreach( $this->getChildReferences() as $childReference ) {
            $child = $childReference->getChild();
            $name = $childReference->getName();
            $map[$name] = $child;
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

    /**
     * @return Collection<int, FragmentReference>
     */
    public function getChildReferences(): Collection {
        return $this->childReferences;
    }

    public function addChildReference(FragmentReference $childReference): static {
        if( !$this->childReferences->contains($childReference) ) {
            $this->childReferences->add($childReference);
            $childReference->setParent($this);
        }

        return $this;
    }

    public function removeChildReference(FragmentReference $childReference): static {
        if( $this->childReferences->removeElement($childReference) ) {
            // set the owning side to null (unless already changed)
            if( $childReference->getParent() === $this ) {
                $childReference->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FragmentReference>
     */
    public function getParentReferences(): Collection {
        return $this->parentReferences;
    }

    public function addParentReference(FragmentReference $parentReference): static {
        if( !$this->parentReferences->contains($parentReference) ) {
            $this->parentReferences->add($parentReference);
            $parentReference->setChild($this);
        }

        return $this;
    }

    public function removeParentReference(FragmentReference $parentReference): static {
        if( $this->parentReferences->removeElement($parentReference) ) {
            // set the owning side to null (unless already changed)
            if( $parentReference->getChild() === $this ) {
                $parentReference->setChild(null);
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

    /**
     * @return Collection<int, FragmentFile>
     */
    public function getFragmentFiles(): Collection {
        return $this->fragmentFiles;
    }

    public function addFragmentFile(FragmentFile $fragmentFile): static {
        if( !$this->fragmentFiles->contains($fragmentFile) ) {
            $this->fragmentFiles->add($fragmentFile);
            $fragmentFile->setFragment($this);
        }

        return $this;
    }

    public function removeFragmentFile(FragmentFile $fragmentFile): static {
        if( $this->fragmentFiles->removeElement($fragmentFile) ) {
            // set the owning side to null (unless already changed)
            if( $fragmentFile->getFragment() === $this ) {
                $fragmentFile->setFragment(null);
            }
        }

        return $this;
    }
}
