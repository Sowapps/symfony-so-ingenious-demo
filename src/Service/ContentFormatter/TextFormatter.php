<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service\ContentFormatter;

use App\Core\ContentFormatterInterface;

readonly class TextFormatter implements ContentFormatterInterface {

    public function format(string $content): string {
        return htmlentities($content, ENT_QUOTES, 'UTF-8');
    }

}
