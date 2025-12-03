<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Sowapps\SoIngenious;

enum TemplatePurpose: string {

    case Article = 'article';
    case Menu = 'menu';
    case Page = 'page';

    public static function values(): array {
        return array_map(fn($case) => $case->value, self::cases());
    }

}
