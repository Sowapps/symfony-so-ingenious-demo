<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use Sowapps\SoCore\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController {
	
	public function home(): Response {
		return $this->render('frontend/page/home.html.twig');
	}
	
}
