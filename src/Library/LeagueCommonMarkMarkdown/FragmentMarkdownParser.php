<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Library\LeagueCommonMarkMarkdown;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

/**
 * Markdown parser for fragment block
 * Recognize [[fragment:<selector>=<value>]] with selector in ["slot", "id"]
 *
 * @see https://commonmark.thephpleague.com/2.x/customization/block-parsing/
 * @see FragmentMarkdownRenderer
 * @see FragmentNode
 * @see \App\Service\FragmentService::getBySelector
 */
class FragmentMarkdownParser extends AbstractBlockContinueParser {

    private FragmentNode $block;

    public function __construct(string $selector, string $value) {
        $this->block = new FragmentNode($selector, $value);
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue {
        return BlockContinue::none();
    }

    public function getBlock(): AbstractBlock {
        return $this->block;
    }

    public static function createStartParser(): BlockStartParserInterface {
        return new class implements BlockStartParserInterface {
            private const START = '[[fragment:';
            private const END = ']]';

            public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart {
                // Dont break indented blocks
                if( $cursor->isIndented() ) {
                    return BlockStart::none();
                }

                // First non-space character
                $cursor->advanceToNextNonSpaceOrTab();

                $line = $cursor->getRemainder();

                // Fast path (perf)
                if( !str_starts_with($line, self::START) ) {
                    return BlockStart::none();
                }

                // Capture only what we need, from START to END
                $start = preg_quote(self::START);
                $end = preg_quote(self::END);
                if( !preg_match('/^' . $start . '\s*([^=\s]+)=([^=\s]+)\s*' . $end . '\s*$/', $line, $m) ) {
                    return BlockStart::none();
                }

                $selector = $m[1];
                $value = $m[2];

                // Important: cursor not shared using v2-parsers, we must provide a "post-parsing" cursor to BlockStart.
                $cursor->advanceToEnd();

                return BlockStart::of(new FragmentMarkdownParser($selector, $value))->at($cursor);
            }
        };
    }
}
