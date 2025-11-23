<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use App\Controller\FragmentPageController;
use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Service for fragment page routing
 *
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html#loading-routes-with-a-custom-service
 */
readonly class FragmentPageRoutingService implements RouteLoaderInterface {

    public function __construct(
        private PageRepository $pageRepository,
    ) {
    }

    public function loadRoutes(): RouteCollection {
        $routes = new RouteCollection();

        $pages = $this->pageRepository->findAll();
        foreach( $pages as $page ) {
            $language = $page->getLanguage();
            $route = new Route($page->getPath(), [
                '_locale'     => $language->getLocale(),
                '_controller' => FragmentPageController::class,
                'id'          => $page->getId(),
            ]);

            $routes->add('fragment-page-' . $page->getId(), $route);
        }

        return $routes;
    }

}
