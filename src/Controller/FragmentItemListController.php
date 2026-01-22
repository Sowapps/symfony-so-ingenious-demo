<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use App\Core\Controller\AbstractFragmentController;
use App\Entity\FragmentRoute;
use App\Entity\PublicationFragment;
use App\Sowapps\SoIngenious\QueryCriteria;
use Sowapps\SoCore\Service\LanguageService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class FragmentItemListController extends AbstractFragmentController {

    public function __construct(
        private readonly LanguageService $languageService
    ) {
    }

    public function __invoke(
        #[MapEntity(id: 'id')] FragmentRoute $route,
        array                                $filters,
        #[MapQueryParameter] bool            $editor = false
    ): Response {
        $requestCriteria = new QueryCriteria($filters);
        $routeCriteria = $route->getItemCriteria() ?? QueryCriteria::empty();
        $criteria = $routeCriteria->withFilters(['purpose' => $route->getItemPurpose()])->and($requestCriteria);
        $items = $this->fragmentService->getCriteriaItems($criteria);
        /** @var PublicationFragment $fragment */
        $fragment = $route->getFragment();
        $values = $this->parseValues($route, $filters);

        return $this->renderFragment($fragment, $editor, [
            'title' => $this->stringService->formatString($fragment->getTitle(), $values),
            'items' => $items,
        ]);
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
