<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Sowapps\SoIngenious\Doctrine\Type;


use App\Sowapps\SoIngenious\QueryCriteria;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;

final class QueryCriteriaType extends JsonType {
    public const NAME = 'query_criteria';

    public function getName(): string {
        return self::NAME;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string {
        if( $value === null ) {
            return null;
        }

        if( !$value instanceof QueryCriteria ) {
            throw ConversionException::conversionFailedInvalidType(
                $value,
                self::NAME,
                [QueryCriteria::class, 'null']
            );
        }

        // On sérialise l’objet en array, puis JsonType gère le JSON
        return parent::convertToDatabaseValue($value->toArray(), $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?QueryCriteria {
        if( $value === null || $value === '' ) {
            return null;
        }

        $array = parent::convertToPHPValue($value, $platform);
        if( $array === null ) {
            return null;
        }

        if( !is_array($array) ) {
            throw ConversionException::conversionFailedFormat(
                $value,
                self::NAME,
                'JSON array'
            );
        }

        return QueryCriteria::fromArray($array);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool {
        // Permet à Doctrine de reconnaître le type même avec du schema tooling
        return true;
    }
}
