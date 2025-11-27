<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service\Routing;

use App\Controller\FragmentController;
use App\Entity\FragmentRoute;
use App\Repository\PageRepository;
use App\Repository\RouteRepository;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Service for fragment page routing
 *
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html#loading-routes-with-a-custom-service
 */
readonly class DatabaseRoutingService implements RouteLoaderInterface {

    public function __construct(
        private RouteRepository $routeRepository,
    ) {
    }

    public function loadRoutes(): RouteCollection {
        $routes = new RouteCollection();

        /** @var \App\Entity\Route[] $appRoutes */
        $appRoutes = $this->routeRepository->findAll();
        foreach( $appRoutes as $appRoute ) {
            if( $appRoute instanceof FragmentRoute ) {
                $language = $appRoute->getLanguage();
                // Routing to standalone page
                $route = new Route($appRoute->getPath(), [
                    '_locale'     => $language->getLocale(),
                    '_controller' => FragmentController::class,
                    'id'          => $appRoute->getId(),
                ]);
                // TODO Routing to single-item page
                // TODO Routing to item-list page
                //            } else if($appRoute instanceof RedirectRoute) {
                // TODO Implement RedirectRoute
            } else {
                throw new RuntimeException(sprintf("Unsupported route #%s", $appRoute->getId()));
            }

            $routes->add('route-' . $appRoute->getId(), $route);
        }

        return $routes;
    }

}
