<?php

namespace App\Twig;

use App\Entity\Fragment;
use App\Repository\RouteRepository;
use App\Service\ContentFormatter\ContentFormatter;
use App\Service\FragmentService;
use App\Service\LanguageService;
use App\Service\Routing\DatabaseRoutingService;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

/**
 * Extension for Twig templating
 */
readonly class AppTwigExtension {

    public function __construct(
        private UrlGeneratorInterface  $urlGenerator,
        private FragmentService        $fragmentService,
        private ContentFormatter       $contentFormatter,
        private RouteRepository        $routeRepository,
        private LanguageService        $languageService,
        private DatabaseRoutingService $databaseRoutingService,
    ) {
    }

    #[AsTwigFilter('richText')]
    public function formatRichText(array $content): string {
        return $this->contentFormatter->formatFromArray($content);
    }

    #[AsTwigFilter('fragment')]
    public function formatFragment(Fragment $fragment, array $parameters = []): string {
        return $this->fragmentService->getFragmentRendering($fragment, ['parameters' => $parameters]);
    }

    #[AsTwigFilter('templateName')]
    public function formatTemplateName(string $value): string {
        return str_replace('/', '--', $value);
    }

    #[AsTwigFunction('route')]
    public function getRoutePath(string $name, array|Fragment|null $parameter = null): string {
        $route = $this->routeRepository->getByName($name, $this->languageService->getActiveLanguage());
        $sfRouteName = $this->databaseRoutingService->getSymfonyRouteName($route, $parameter);
        return $this->urlGenerator->generate($sfRouteName);
    }

    #[AsTwigFunction('slot')]
    public function getSlotFragment(string $name): Fragment {
        $fragment = $this->fragmentService->getSlotFragment($name);
        if( !$fragment ) {
            throw new RuntimeException(sprintf('Fragment of slot "%s" not found', $name));
        }
        return $fragment;
    }

}
