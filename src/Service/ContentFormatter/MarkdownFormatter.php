<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service\ContentFormatter;

use App\Core\ContentFormatterInterface;
use App\Library\LeagueCommonMarkMarkdown\SoIngeniousExtension;
use App\Service\FragmentService;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

readonly class MarkdownFormatter implements ContentFormatterInterface {

    private MarkdownConverter $converter;

    public function __construct(
        private FragmentService $fragmentService
    ) {
        $this->converter = $this->build();
    }

    private function build(): MarkdownConverter {
        $env = new Environment([
            'html_input'         => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $env->addExtension(new CommonMarkCoreExtension());
        $env->addExtension(new AttributesExtension());
        $env->addExtension(new SoIngeniousExtension($this->fragmentService));

        return new MarkdownConverter($env);
    }

    /**
     * @throws CommonMarkException
     */
    public function format(string $content): string {
        return $this->converter->convert($content);
    }

}
