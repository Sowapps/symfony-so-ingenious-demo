<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use App\Entity\FragmentRoute;
use App\Service\FragmentService;
use Sowapps\SoCore\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FragmentController extends AbstractController {

    public function __construct(
        private readonly FragmentService $fragmentService,
    ) {
    }

    public function __invoke(FragmentRoute $route): Response {
        return new Response($this->fragmentService->getFragmentRendering($route->getFragment()));
        //        return new Response(); // TODO
        //        return new Response($this->fragmentService->getFragmentRendering($page->getFragment()));
    }

}
