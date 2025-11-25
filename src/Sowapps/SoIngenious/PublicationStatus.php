<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Sowapps\SoIngenious;

enum PublicationStatus: string {

    case Draft = 'draft';
    case Published = 'published';
    case Scheduled = 'scheduled';// TODO

    public static function values(): array {
        return array_map(fn($case) => $case->value, self::cases());
    }

}
