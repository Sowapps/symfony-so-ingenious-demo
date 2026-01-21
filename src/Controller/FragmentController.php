<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use App\Core\Controller\AbstractFragmentController;
use App\Entity\FragmentRoute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class FragmentController extends AbstractFragmentController {

    public function __invoke(
        Request                   $request,
        FragmentRoute             $route,
        #[MapQueryParameter] bool $editor = false
    ): Response {
        return $this->renderFragment($route->getFragment(), $editor);
    }

}
