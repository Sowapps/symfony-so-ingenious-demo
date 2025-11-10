<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use Sowapps\SoCore\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController {

    #[Route("/", name: "app_index")]
	public function index(): Response {
		return $this->render('frontend/page/home.html.twig');
	}

}
