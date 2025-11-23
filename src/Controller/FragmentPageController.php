<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use App\Entity\Page;
use App\Service\FragmentService;
use Sowapps\SoCore\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FragmentPageController extends AbstractController {

    public function __construct(
        private readonly FragmentService $fragmentService,
    ) {
    }

    public function __invoke(Page $page): Response {
        return new Response($this->fragmentService->getFragmentRendering($page->getFragment()));
    }

}
