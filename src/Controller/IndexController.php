<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use BadMethodCallException;
use Sowapps\SoCore\Core\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController {

    #[Route("/", name: "app_index")]
    public function index(): void {
        // Normally you can never get to this controller
        // The LocaleSubscriber service should redirect this page before reaching this point
        throw new BadMethodCallException('Not implemented');
    }

}
