<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

readonly class TextFormatter {
    /**
     * TextFormatter constructor
     *
     */
    public function __construct(
        private TranslatorInterface $translator,
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
