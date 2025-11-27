<?php

namespace App\Entity;

use App\Repository\FragmentRouteRepository;
use App\Sowapps\SoIngenious\FragmentRouting;
use App\Sowapps\SoIngenious\QueryCriteria;
use App\Sowapps\SoIngenious\TemplatePurpose;
use Doctrine\ORM\Mapping as ORM;

/**
 * Lead to a standalone page, one item page or item list page
 * The page is the fragment, item* properties are related to the embedded item
 */
#[ORM\Entity(repositoryClass: FragmentRouteRepository::class)]
class FragmentRoute extends Route {
    /**
     * Item name in a menu
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $menuName = null;

    /**
     * Routing usage
     */
    #[ORM\Column(length: 255, nullable: true, enumType: FragmentRouting::class)]
    private ?FragmentRouting $routing = null;

    #[ORM\OneToOne(inversedBy: 'route', cascade: ['persist', 'remove'])]
    private ?Fragment $fragment = null;

    /**
     * Purpose of embedded item(s) in fragment
     * If routing item list or single item
     */
    #[ORM\Column(length: 255, nullable: true, enumType: TemplatePurpose::class)]
    private ?TemplatePurpose $itemPurpose = null;

    #[ORM\Column(type: 'query_criteria', nullable: true)]
    private ?QueryCriteria $itemCriteria = null;

    public function getPathValues(): array {
        return preg_match_all('#\{([^\}]+)\}#', $this->getPath(), $matches) ? $matches[1] : [];
    }

    public function getMenuName(): ?string {
        return $this->menuName;
    }

    public function setMenuName(?string $menuName): FragmentRoute {
        $this->menuName = $menuName;
        return $this;
    }

    public function getRouting(): ?FragmentRouting {
        return $this->routing;
    }

    public function setRouting(?FragmentRouting $routing): FragmentRoute {
        $this->routing = $routing;
        return $this;
    }

    public function getFragment(): ?Fragment {
        return $this->fragment;
    }

    public function setFragment(?Fragment $fragment): static {
        $this->fragment = $fragment;

        return $this;
    }

    public function getItemPurpose(): ?TemplatePurpose {
        return $this->itemPurpose;
    }

    public function setItemPurpose(?TemplatePurpose $itemPurpose): FragmentRoute {
        $this->itemPurpose = $itemPurpose;
        return $this;
    }

    public function getItemCriteria(): ?QueryCriteria {
        return $this->itemCriteria;
    }

    public function setItemCriteria(?QueryCriteria $itemCriteria): FragmentRoute {
        $this->itemCriteria = $itemCriteria;
        return $this;
    }
}
