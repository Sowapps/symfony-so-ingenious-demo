<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Library\LeagueCommonMarkMarkdown;

use App\Service\FragmentService;
use InvalidArgumentException;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Stringable;

/**
 * Markdown parser for fragment block
 *
 * @see https://commonmark.thephpleague.com/2.x/customization/rendering/
 * @see FragmentMarkdownParser
 * @see FragmentNode
 */
class FragmentMarkdownRenderer implements NodeRendererInterface {

    public function __construct(
        private readonly FragmentService $fragmentService,
    ) {
    }

    /**
     * @param FragmentNode $node
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable|string|null {
        if( !$node instanceof FragmentNode ) {
            throw new InvalidArgumentException(sprintf(
                'Invalid node type, expected FragmentNode, got "%s"',
                $node::class
            ));
        }

        $fragment = $this->fragmentService->getBySelector($node->selector, $node->value);

        if( !$fragment ) {
            throw new InvalidArgumentException(sprintf('No fragment found with criteria %s=%s', $node->selector, $node->value));
        }

        return new HtmlElement(
            'div',
            [
                'class'                  => 'md md-fragment',
                'data-fragment-criteria' => $node->selector,
                'data-fragment-value'    => $node->value,
            ],
            $this->fragmentService->renderFragment($fragment)
        );
    }

}
