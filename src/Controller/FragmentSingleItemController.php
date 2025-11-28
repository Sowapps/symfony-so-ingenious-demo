<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use App\Entity\Fragment;
use App\Entity\FragmentRoute;
use App\Service\FragmentService;
use Sowapps\SoCore\Core\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;

class FragmentSingleItemController extends AbstractController {

    public function __construct(
        private readonly FragmentService $fragmentService,
    ) {
    }

    public function __invoke(
        #[MapEntity(id: 'id')]
        FragmentRoute $route,
        #[MapEntity(id: 'itemId')]
        Fragment      $itemFragment
    ): Response {
        return new Response($this->fragmentService->getFragmentRendering($route->getFragment(), ['item' => $itemFragment]));
    }

}
