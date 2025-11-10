<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use App\Sowapps\Core\Types\SoBigInteger;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class TextFormatter {


    /**
     * TextFormatter constructor
     *
     */
    public function __construct(
        private TranslatorInterface $translator,
        //        private TranslatorInterface $translator,
    ) {
    }

    public function translate(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }
	
	/**
	 * Format with seconds precision
	 *
	 * @param int $seconds
	 * @return string
	 */
	public function formatDurationToSeconds(int $seconds): string {
		if( $seconds < 60 ) { // Before one minute
			$format = 's \s\e\c';
		} elseif( $seconds < 3600 ) { // Before one hour
			$format = 'i \m\i\n s \s';
		} elseif( $seconds < 86400 ) { // Before one day
			$format = 'H \h\o\u\r\s i \m s \s';
		} else { // Any number of days
			$format = 'z \d\a\y\s H:i:s';
		}
		
		return gmdate($format, $seconds);
	}

    public function formatQuantityText(SoBigInteger $intQuantity, bool $signed = false, ?string $suffix = null): string {
//        global $LOGGER; // TODO Remove Debug
//        $LOGGER->debug('formatQuantityText - $intQuantity : ' . $intQuantity->getValue());// Debug
        $quantity = $intQuantity->divide(1000);// Precise DB Integer quantity to real decimal quantity
//        $LOGGER->debug('formatQuantityText - $intQuantity : ' . $quantity->getValue());// Debug
        $unitSb = null;// For IDE
        foreach( self::getUnits() as [$unit, $unitSb] ) {
            if( !$quantity->lessThan(100000) ) {
                // Go to next unit
//                $LOGGER->debug('Unit calculate - ' . $unit . ' : ' . $quantity->getValue() . ' / 1000 = ' . bcdiv($quantity->getValue(), 1000, 2));
                $quantity = $quantity->divide(1000);
            } else {
                // Stop to this unit
                break;
            }
        }
        // Quantity was reduced to compatible float number
        $quantityFloat = $quantity->asFloat();
//        $LOGGER->debug('formatQuantityText - $quantityFloat : ' . $quantityFloat);// Debug
        $withDecimals = $quantityFloat < 1000;
        $formatted = number_format($quantityFloat, $withDecimals ? 2 : 0) . ($unitSb ? ' ' . $unitSb : '');
        return ($signed ? $this->getNumberSign($quantityFloat) : '') . $formatted . ($suffix !== null ? ' ' . $suffix : '');
    }

    protected function getNumberSign(float $quantityFloat): string {
        return $quantityFloat < 0 ? '-' : '+';
    }

    public static function getUnits(): array {
        return [
            ['unit', null],
            ['thousand', 'K'],
            ['million', 'M'],
            ['billion', 'G'],
            ['trillion', 'T'],
            ['quadrillion', 'P'],
            ['quintillion', 'E'],
            ['sextillion', 'Z'],
            ['septillion', 'Y'],
            ['octillion', 'R'],
            ['nonillion', 'Q'],
            //            ['decillion', 'K'],
            //            ['undecillion', 'K'],
            //            ['duodecillion', 'K'],
            //            ['tredecillion', 'K'],
            //            ['quattuordecillion', 'K'],
            //            ['quindecillion', 'K'],
            //            ['sexdecillion', 'K'],
            //            ['septendecillion', 'K'],
            //            ['octodecillion', 'K'],
            //            ['novemdecillion', 'K'],
            //            ['vigintillion', 'K'],
            //            ['centillion', 'K'],
        ];
    }


}
