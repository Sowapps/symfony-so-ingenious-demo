<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service\Routing;

use App\Controller\FragmentController;
use App\Controller\FragmentItemListController;
use App\Controller\FragmentSingleItemController;
use App\Controller\RedirectController;
use App\Entity\Fragment;
use App\Entity\FragmentRoute;
use App\Entity\RedirectRoute;
use App\Entity\Route;
use App\Repository\PublicationFragmentRepository;
use App\Repository\RouteRepository;
use App\Sowapps\SoIngenious\FragmentRouting;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use Throwable;

/**
 * Service for fragment page routing
 *
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html#loading-routes-with-a-custom-service
 */
readonly class DatabaseRoutingService implements RouteLoaderInterface {

    public function __construct(
        private LoggerInterface               $logger,
        private PropertyAccessorInterface     $propertyAccessor,
        private RouteRepository               $routeRepository,
        private PublicationFragmentRepository $publicationFragmentRepository,
    ) {
    }

    public function loadRoutes(): RouteCollection {
        $routes = new RouteCollection();

        /** @var Route[] $appRoutes */
        $appRoutes = $this->routeRepository->findAll();
        foreach( $appRoutes as $route ) {
            try {
                if( $route instanceof FragmentRoute ) {
                    // Routing to standalone page
                    switch( $route->getRouting() ) {
                        case FragmentRouting::ItemList:
                            $routeValueKeys = $route->getPathValues();
                            if( $routeValueKeys ) {
                                // Has any filter in path -> Get all available values for these filters
                                $listFilters = $this->publicationFragmentRepository->getListFilters();
                                //                            $itemCriteria = $appRoute->getItemCriteria() ?? QueryCriteria::empty();
                                $itemPurpose = $route->getItemPurpose();
                                if( !$itemPurpose ) {
                                    throw new RuntimeException('No purpose in fragment single item routing, the itemPurpose is required');
                                }
                                // Builder Query
                                $query = $this->publicationFragmentRepository->query();
                                $selects = [];
                                foreach( $routeValueKeys as $key ) {
                                    $filterSelect = $listFilters[$key] ?? null;
                                    if( !$filterSelect ) {
                                        throw new RuntimeException(sprintf('Filter "%s" not found for a route', $key));
                                    }
                                    $selects[] = $filterSelect . ' AS ' . $key;
                                }
                                // Set selected columns, replace previous
                                $query->select(...$selects)->distinct();
                                /** @var array[] $values */
                                $values = $query->getQuery()->getArrayResult();
                                foreach( $values as $routeValues ) {
                                    $this->addRoute($routes, $route, $this->getSymfonyRouteName($route, $routeValues), FragmentItemListController::class, $routeValues, [
                                        'filters' => $routeValues,
                                    ]);
                                }
                            } else {
                                // Has no filter in path -> only one global route
                                $this->addRoute($routes, $route, $this->getSymfonyRouteName($route), FragmentItemListController::class, [], [
                                    'filters' => [],
                                ]);
                            }
                            break;
                        case FragmentRouting::SingleItem:
                            $listFilters = $this->publicationFragmentRepository->getSingleFilters();
                            [$identifier] = $route->getPathValues();// Only one identifying filter is possible
                            // $identifier : slug, id
                            $itemPurpose = $route->getItemPurpose();
                            $filterSelect = $listFilters[$identifier] ?? null;
                            if( !$itemPurpose ) {
                                throw new RuntimeException('No purpose in fragment single item routing, the itemPurpose is required');
                            }
                            if( !$filterSelect ) {
                                throw new RuntimeException(sprintf('Filter "%s" not found for a route', $identifier));
                            }
                            // List all fragments that could be an item
                            /** @var Fragment[] $itemList */
                            $itemList = $this->publicationFragmentRepository->findBy(['purpose' => $itemPurpose]);
                            foreach( $itemList as $itemFragment ) {
                                $identifierValue = $this->propertyAccessor->getValue($itemFragment, $identifier);
                                $this->addRoute($routes, $route, $this->getSymfonyRouteName($route, $itemFragment), FragmentSingleItemController::class, [
                                    $identifier => $identifierValue,
                                ], [
                                    'itemId' => $itemFragment->getId(),
                                ]);
                            }
                            break;
                        case FragmentRouting::Standalone:
                            $this->addRoute($routes, $route, $this->getSymfonyRouteName($route), FragmentController::class);
                            break;
                    }

                } else if( $route instanceof RedirectRoute ) {
                    $this->addRoute($routes, $route, $this->getSymfonyRouteName($route), RedirectController::class);

                } else {
                    throw new RuntimeException('Unsupported route');
                }
            } catch( Throwable $exception ) {
                $this->logger->error('Error with route {routeId} {routeClass} : {message}', [
                    'routeId'    => $route->getId(),
                    'routeClass' => $route::class,
                    'message'    => $exception->getMessage(),
                ]);
            }

        }

        return $routes;
    }

    protected function addRoute(RouteCollection $routes, Route $route, string $name, string $controller, array $pathValues = [], array $parameters = []): void {
        $path = $route->getPath();
        if( $pathValues ) {
            $routeValueKeys = array_map(fn($key) => '{' . $key . '}', array_keys($pathValues));
            $path = str_replace($routeValueKeys, array_values($pathValues), $path);
        }
        $routes->add($name, new SymfonyRoute($path, [
                '_locale'     => $route->getLanguage()->getLocale(),
                '_controller' => $controller,
                'id'          => $route->getId(),
            ] + $parameters));
    }

    protected function getValuesId(array $values): string {
        return substr(md5(json_encode($values)), 0, 8);
    }

    public function getSymfonyRouteName(Route $route, array|Fragment|null $parameter = null): ?string {
        $key = null;
        if( !$key ) {
            if( $route instanceof FragmentRoute ) {
                switch( $route->getRouting() ) {
                    case FragmentRouting::ItemList:
                        $parameter ??= [];
                        if( !is_array($parameter) ) {
                            throw new RuntimeException(sprintf('Parameter must be an array for the item list page #%s %s', $route->getId(), $route->getPath()));
                        }
                        $key = $route->getId() . '-list' . ($parameter ? '-' . $this->getValuesId($parameter) : '');
                        break;
                    case FragmentRouting::SingleItem:
                        if( !$parameter instanceof Fragment ) {
                            throw new RuntimeException(sprintf('Parameter must be a fragment for the single item page #%s %s', $route->getId(), $route->getPath()));
                        }
                        $key = $route->getId() . '-single-' . $parameter->getId();
                        break;
                    case FragmentRouting::Standalone:
                        if( $parameter ) {
                            throw new RuntimeException(sprintf('No parameter for the standalone page #%s %s', $route->getId(), $route->getPath()));
                        }
                        $key = $route->getId() . '-standalone';
                        break;
                }
            } else if( $route instanceof RedirectRoute ) {
                $key = $route->getId() . '-redirect';
            }
        }

        if( !$key ) {
            throw new RuntimeException(sprintf('Route #%s has no name', $route->getId()));
        }
        return sprintf('dr-%s-%s', $route->getLanguage()->getLocale(), $key);
    }

}
