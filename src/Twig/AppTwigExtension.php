<?php

namespace App\Twig;

use App\Entity\Fragment;
use App\Service\ContentFormatter\ContentFormatter;
use App\Service\FragmentService;
use Twig\Attribute\AsTwigFilter;

/**
 * Extension for Twig templating
 */
readonly class AppTwigExtension {

    public function __construct(
        private FragmentService $fragmentService,
        private ContentFormatter $contentFormatter,
    ) {
    }

    #[AsTwigFilter('richText')]
    public function formatRichText(array $content): string {
        return $this->contentFormatter->formatFromArray($content);
    }

    #[AsTwigFilter('fragment')]
    public function formatFragment(Fragment $fragment): string {
        return $this->fragmentService->getFragmentRendering($fragment);
    }

    #[AsTwigFilter('templateName')]
    public function formatTemplateName(string $value): string {
        return str_replace('/', '--', $value);
    }

}
