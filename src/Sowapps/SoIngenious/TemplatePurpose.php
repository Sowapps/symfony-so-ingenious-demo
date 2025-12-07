<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Sowapps\SoIngenious;

enum TemplatePurpose: string {

    case Article = 'article'; // Article content
    case Menu = 'menu'; // Used for menus
    case Page = 'page'; // Routable
    case Widget = 'widget'; // Reusable as widget anywhere in app

    public static function values(): array {
        return array_map(fn($case) => $case->value, self::cases());
    }

}
