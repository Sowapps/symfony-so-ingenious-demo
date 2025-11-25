<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use App\Entity\Article;
use Sowapps\SoCore\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FragmentArticleSingleController extends AbstractController {

    //    public function __construct(
    //        private readonly FragmentService $fragmentService,
    //    ) {
    //    }

    public function __invoke(Article $article): Response {
        // TODO add article in standalone article page
        // $this->fragmentService->getFragmentRendering($article->getFragment())
        return $this->render('frontend/fragment-article-single.html.twig', [
            'article' => $article,
        ]);
    }

}
