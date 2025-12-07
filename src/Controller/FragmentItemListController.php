<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use App\Entity\FragmentRoute;
use App\Service\FragmentService;
use App\Sowapps\SoIngenious\QueryCriteria;
use Sowapps\SoCore\Core\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;

class FragmentItemListController extends AbstractController {

    public function __construct(
        private readonly FragmentService $fragmentService,
    ) {
    }

    public function __invoke(
        #[MapEntity(id: 'id')]
        FragmentRoute $route,
        array         $filters
    ): Response {
        $requestCriteria = new QueryCriteria($filters);
        $routeCriteria = $route->getItemCriteria() ?? QueryCriteria::empty();
        $criteria = $routeCriteria->withFilters(['purpose' => $route->getItemPurpose()])->and($requestCriteria);
        $items = $this->fragmentService->getCriteriaItems($criteria);
        return new Response($this->fragmentService->getFragmentRendering($route->getFragment(), ['items' => $items]));
    }

}
