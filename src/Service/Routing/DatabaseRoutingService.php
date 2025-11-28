<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service\Routing;

use App\Controller\FragmentController;
use App\Controller\FragmentSingleItemController;
use App\Entity\Fragment;
use App\Entity\FragmentRoute;
use App\Repository\FragmentRepository;
use App\Repository\RouteRepository;
use App\Sowapps\SoIngenious\FragmentRouting;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Service for fragment page routing
 *
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html#loading-routes-with-a-custom-service
 */
readonly class DatabaseRoutingService implements RouteLoaderInterface {

    public function __construct(
        private PropertyAccessorInterface $propertyAccessor,
        private RouteRepository           $routeRepository,
        private FragmentRepository        $fragmentRepository,
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
                switch( $appRoute->getRouting() ) {
                    case FragmentRouting::ItemList:// TODO ItemList routing
                        throw new RuntimeException('To be implemented');
                    case FragmentRouting::SingleItem:
                        // List all fragments that could be an item
                        $filters = $this->fragmentRepository->getSingleFilters();
                        [$identifier] = $appRoute->getPathValues();// Only one identifying filter is possible
                        // $identifier : slug, id
                        $itemPurpose = $appRoute->getItemPurpose();
                        $filterQuery = $filters[$identifier] ?? null;
                        if( !$itemPurpose ) {
                            throw new RuntimeException(sprintf('No purpose in fragment single item routing #%s, the itemPurpose is required', $appRoute->getId()));
                        }
                        if( !$filterQuery ) {
                            throw new RuntimeException(sprintf('Filter "%s" not found for a fragment', $identifier));
                        }
                        /** @var Fragment[] $itemList */
                        $itemList = $this->fragmentRepository->findBy(['purpose' => $itemPurpose]);
                        //                            ->query()
                        //                            ->getQuery()
                        //                            ->getArrayResult();
                        //                        ;
                        foreach( $itemList as $itemFragment ) {
                            $identifierValue = $this->propertyAccessor->getValue($itemFragment, $identifier);
                            $path = str_replace('{' . $identifier . '}', $identifierValue, $appRoute->getPath());
                            $route = new Route($path, [
                                '_locale'     => $language->getLocale(),
                                '_controller' => FragmentSingleItemController::class,
                                'id'          => $appRoute->getId(),
                                'itemId'      => $itemFragment->getId(),
                            ]);
                            $routes->add('route-' . $appRoute->getId() . '-single-' . $itemFragment->getId(), $route);
                        }
                        break;
                    case FragmentRouting::Standalone:
                        $route = new Route($appRoute->getPath(), [
                            '_locale'     => $language->getLocale(),
                            '_controller' => FragmentController::class,
                            'id'          => $appRoute->getId(),
                        ]);
                        $routes->add('route-' . $appRoute->getId(), $route);
                        break;
                }
                // TODO Routing to single-item page
                // TODO Routing to item-list page
                //            } else if($appRoute instanceof RedirectRoute) {
                // TODO Implement RedirectRoute
            } else {
                throw new RuntimeException(sprintf("Unsupported route #%s", $appRoute->getId()));
            }

        }

        return $routes;
    }

}
