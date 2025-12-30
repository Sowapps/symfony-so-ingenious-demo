<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Library\LeagueCommonMarkMarkdown;

use App\Service\FragmentService;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * @see https://commonmark.thephpleague.com/2.x/customization/extensions/
 */
final readonly class SoIngeniousExtension implements ExtensionInterface {

    public function __construct(
        private FragmentService $fragmentService,
    ) {
    }

    public function register(EnvironmentBuilderInterface $environment): void {
        $environment
            ->addBlockStartParser(FragmentMarkdownParser::createStartParser())
            ->addRenderer(FragmentNode::class, new FragmentMarkdownRenderer($this->fragmentService));
    }
}
