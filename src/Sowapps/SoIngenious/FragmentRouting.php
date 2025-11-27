<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Sowapps\SoIngenious;

/**
 * Content type for their routing and relation with other fragments
 */
enum FragmentRouting: string {

    case Standalone = 'standalone'; // The page contains itself all the content (like home page)
    case SingleItem = 'single_item'; // The page embeds one item (like an article, the path contains an identifier to this item)
    case ItemList = 'item_list'; // The page embeds a list of items of same type (like a blog article list, the path contains filters for the filtering query)

    public static function values(): array {
        return array_map(fn($case) => $case->value, self::cases());
    }

}
