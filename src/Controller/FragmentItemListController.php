<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use App\Entity\FragmentRoute;
use App\Entity\PublicationFragment;
use App\Repository\PublicationFragmentRepository;
use App\Service\FragmentService;
use App\Sowapps\SoIngenious\QueryCriteria;
use Doctrine\Common\Collections\Criteria;
use RuntimeException;
use Sowapps\SoCore\Core\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;

class FragmentItemListController extends AbstractController {

    public function __construct(
        private readonly FragmentService               $fragmentService,
        private readonly PublicationFragmentRepository $publicationFragmentRepository,
    ) {
    }

    public function __invoke(
        #[MapEntity(id: 'id')]
        FragmentRoute $route,
        array         $filters
    ): Response {
        $items = $this->getItems($route, $filters);
        return new Response($this->fragmentService->getFragmentRendering($route->getFragment(), ['items' => $items]));
    }

    /**
     * @return PublicationFragment[]
     */
    private function getItems(FragmentRoute $route, array $filters): array {
        $itemCriteria = $route->getItemCriteria() ?? QueryCriteria::empty();
        $itemPurpose = $route->getItemPurpose();

        // Build Doctrine Criteria
        $criteria = Criteria::create();
        if( $itemCriteria->getOrderBy() ) {
            // Optional for listing all paths
            $criteria->orderBy($itemCriteria->getOrderBy());
        }
        if( $itemCriteria->getLimit() !== null ) {
            $criteria->setMaxResults($itemCriteria->getLimit());
        }
        $listFilters = $this->publicationFragmentRepository->getListFilters();
        $query = $this->publicationFragmentRepository->query();
        $qe = $query->expr();
        $eb = Criteria::expr();
        $conditionAndX = $qe->andX();
        $criteria->andWhere($eb->eq('purpose', $itemPurpose));


        foreach( $filters as $filterName => $filterValue ) {
            $filterSelect = $listFilters[$filterName] ?? null;
            if( !$filterSelect ) {
                throw new RuntimeException(sprintf('Filter "%s" not found for a route', $filterName));
            }
            $conditionAndX->add($qe->eq($filterSelect, ':' . $filterName));
            $query->setParameter($filterName, $filterValue);
        }

        $query = $query->addCriteria($criteria);
        if( $conditionAndX->count() ) {
            // Add complex filters
            $query->andWhere($conditionAndX);
        }

        return $query
            ->getQuery()
            ->getResult();
    }

}
