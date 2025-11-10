<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Exception;

use InvalidArgumentException;
use JsonSerializable;
use Throwable;

abstract class AbstractValidationException extends InvalidArgumentException implements JsonSerializable {
	
	public function __construct(?Throwable $previous = null) {
		parent::__construct('', 400, $previous);
	}
	
}
