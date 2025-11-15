<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service\ContentFormatter;

use App\Core\ContentFormatterInterface;

readonly class HtmlFormatter implements ContentFormatterInterface {

    public function format(string $content): string {
        return $content;
    }

}
