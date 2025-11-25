<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service\Routing;

use App\Controller\FragmentArticleSingleController;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Service for article routing
 *
 * @see https://symfony.com/doc/current/routing/custom_route_loader.html#loading-routes-with-a-custom-service
 */
readonly class FragmentArticleRoutingService implements RouteLoaderInterface {

    public function __construct(
        private ArticleRepository $articleRepository,
    ) {
    }

    public function loadRoutes(): RouteCollection {
        $routes = new RouteCollection();

        $articles = $this->articleRepository->listEnabled();
        foreach( $articles as $article ) {
            $language = $article->getLanguage();
            $route = new Route($this->getSingleArticlePath($article), [
                '_locale'     => $language->getLocale(),
                '_controller' => FragmentArticleSingleController::class,
                'id'          => $article->getId(),
            ]);

            $routes->add('fragment-article-' . $article->getId(), $route);
        }

        return $routes;
    }

    public function getSingleArticlePath(Article $article): string {
        return '/article/' . $article->getSlug();
    }

}
