<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use App\Entity\FragmentRoute;
use App\Service\FragmentService;
use App\Sowapps\SoIngenious\QueryCriteria;
use Sowapps\SoCore\Core\Controller\AbstractController;
use Sowapps\SoCore\Service\LanguageService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;

class FragmentItemListController extends AbstractController {

    public function __construct(
        private readonly LanguageService $languageService,
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
        $fragment = $route->getFragment();
        $values = $this->parseValues($route, $filters);

        return new Response($this->fragmentService->getFragmentRendering($fragment, [
            'title' => $this->stringService->formatString($fragment->getTitle(), $values),
            'items' => $items,
        ]));
    }

    protected function parseValues(FragmentRoute $route, array $filters): array {
        $values = $filters;
        if( isset($values['month']) ) {
            // TODO Implement a dynamic way to do this
            $values['monthText'] = ucfirst($this->languageService->formatMonth($values['month']));
        }

        return $values;
    }

}
