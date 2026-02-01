<?php

namespace App\Twig;

use App\Entity\Fragment;
use App\Entity\PublicationFragment;
use App\Repository\RouteRepository;
use App\Service\ContentFormatter\ContentFormatter;
use App\Service\FragmentService;
use App\Service\Routing\DatabaseRoutingService;
use App\Sowapps\SoIngenious\QueryCriteria;
use Doctrine\ORM\Query\QueryException;
use RuntimeException;
use Sowapps\SoCore\Entity\File;
use Sowapps\SoCore\Service\FileService;
use Sowapps\SoCore\Service\LanguageService;
use Symfony\Component\Form\FormView;
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
        private FileService $fileService,
    ) {
    }

    #[AsTwigFilter('criteria')]
    public function formatCriteria(array $criteria): QueryCriteria {
        return QueryCriteria::fromArray($criteria);
    }

    /**
     * Get list from criteria
     * For now, it only handles Fragment entities
     * @param QueryCriteria $criteria
     * @return PublicationFragment[]
     * @throws QueryException
     */
    #[AsTwigFilter('list')]
    public function getList(QueryCriteria $criteria): array {
        return $this->fragmentService->getCriteriaItems($criteria);
    }

    #[AsTwigFilter('richText')]
    public function formatRichText(array $content): string {
        return $this->contentFormatter->formatFromArray($content);
    }

    #[AsTwigFilter('fragment')]
    public function formatFragment(Fragment $fragment, array $parameters = []): string {
        return $this->fragmentService->getFragmentRendering($fragment, ['parameters' => $parameters]);
    }

    #[AsTwigFilter('fileUrl')]
    public function getFileUrl(File $file, bool $download = false): string {
        return $this->fileService->getFileUrl($file, $download);
    }

    #[AsTwigFilter('templateName')]
    public function formatTemplateName(string $value): string {
        return str_replace('/', '--', $value);
    }

    // TODO Move to SoCore
    #[AsTwigFilter('attributes', isSafe: ['html'])]
    public function formatAttributes(FormView|array $attributes): string {
        if( $attributes instanceof FormView ) {
            $attributes = $attributes->vars['attr'];
        }
        $html = '';
        foreach( $attributes as $key => $value ) {
            if( $value === null || $value === false || $value === '' ) {
                continue;
            }
            $html .= ' ' . ($value === true ? $key : $key . '="' . $value . '"');
        }

        return $html;
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

    #[AsTwigFunction('breakpoint')]
    public function breakpoint(): void {
        xdebug_break();
    }

}
