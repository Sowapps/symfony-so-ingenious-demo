<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use App\Core\Controller\AbstractFragmentController;
use App\Entity\Fragment;
use App\Entity\FragmentRoute;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class FragmentSingleItemController extends AbstractFragmentController {

    public function __invoke(
        #[MapEntity(id: 'id')] FragmentRoute $route,
        #[MapEntity(id: 'itemId')] Fragment  $itemFragment,
        #[MapQueryParameter] bool            $editor = false
    ): Response {
        return $this->renderFragment($route->getFragment(), $editor, ['item' => $itemFragment]);
    }

}
