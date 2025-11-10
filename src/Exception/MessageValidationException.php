<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Exception;

use Throwable;

class MessageValidationException extends AbstractValidationException {
	
	/**
	 * MessageValidationException constructor
	 *
	 * @param string[] $errors
	 */
	public function __construct(
		protected readonly array $errors,
		?Throwable               $previous = null
	) {
		parent::__construct($previous);
	}
	
	public function getErrors(): array {
		return $this->errors;
	}
	
	public function jsonSerialize(): array {
		$errors = [];
		foreach( $this->getErrors() as $error ) {
			$errors[] = ['message' => $error];
		}
		
		return [
			'errors' => $errors,
		];
	}
	
}
