<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Core;

/**
 * Contract for a generic content formatter
 */
interface ContentFormatterInterface {

    public function format(string $content): string;

}
