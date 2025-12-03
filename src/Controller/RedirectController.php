<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use App\Entity\RedirectRoute;
use Sowapps\SoCore\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends AbstractController {

    public function __invoke(RedirectRoute $route): Response {
        return $this->redirect($route->getTargetPath());
    }

}
