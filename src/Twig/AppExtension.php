<?php

namespace App\Twig;

use Twig\Attribute\AsTwigFilter;

class AppExtension {

    #[AsTwigFilter('richText')]
    public function formatRichText(array $richText): string {
        return $richText['text'];
    }

    #[AsTwigFilter('templateName')]
    public function formatTemplateName(string $value): string {
        return str_replace('/', '--', $value);
    }

}
