<?php

namespace App\Twig;

use App\Entity\Fragment;
use App\Service\ContentFormatter\ContentFormatter;
use App\Service\FragmentService;
use RuntimeException;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

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
    public function formatFragment(Fragment $fragment, array $parameters = []): string {
        return $this->fragmentService->getFragmentRendering($fragment, ['parameters' => $parameters]);
    }

    #[AsTwigFilter('templateName')]
    public function formatTemplateName(string $value): string {
        return str_replace('/', '--', $value);
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
