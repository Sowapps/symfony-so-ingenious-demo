<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Library\LeagueCommonMarkMarkdown;

use League\CommonMark\Node\Block\AbstractBlock;

class FragmentNode extends AbstractBlock {

    public function __construct(
        public readonly string $selector,
        public readonly string $value,
    ) {
        parent::__construct();
    }

}
