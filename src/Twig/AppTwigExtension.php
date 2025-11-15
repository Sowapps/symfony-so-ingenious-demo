<?php

namespace App\Twig;

use App\Service\ContentFormatter\ContentFormatter;
use Twig\Attribute\AsTwigFilter;

/**
 * Extension for Twig templating
 */
readonly class AppTwigExtension {

    public function __construct(
        private ContentFormatter $contentFormatter,
    ) {
    }

    #[AsTwigFilter('richText')]
    public function formatRichText(array $content): string {
        return $this->contentFormatter->formatFromArray($content);
    }

    #[AsTwigFilter('templateName')]
    public function formatTemplateName(string $value): string {
        return str_replace('/', '--', $value);
    }

}
